<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Registration;
use App\Models\LoyaltyCoupon;
use App\Models\CouponRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BrandPortalController extends Controller
{
    /**
     * Middleware de protección incorporado en constructor para rutas específicas.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['showLoginForm', 'login']);
    }

    /**
     * Muestra el formulario de Login para el Portal de Marcas.
     */
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->brands()->exists()) {
            return redirect()->route('brand.dashboard');
        }
        return view('brand.login');
    }

    /**
     * Procesa el inicio de sesión.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, true)) {
            $user = Auth::user();
            
            // Verificar que el usuario pertenezca al menos a una marca
            if (!$user->brands()->exists()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Este usuario no está asociado a ninguna marca/stand.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('brand.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Vista principal del Stand de Marcas (Dashboard / Escáner).
     */
    public function dashboard()
    {
        $brand = Auth::user()->brands()->first(); // Asumimos el primero si tiene múltiples

        if (!$brand) {
            Auth::logout();
            return redirect()->route('brand.login')->withErrors(['error' => 'Sin marcas asociadas.']);
        }

        // Contar redenciones realizadas hoy por este stand para la estadística rápida
        $todayRedemptions = CouponRedemption::whereHas('loyaltyCoupon', function($q) use ($brand) {
            $q->where('brand_id', $brand->id);
        })
        ->whereDate('redeemed_at', now()->toDateString())
        ->count();

        return view('brand.dashboard', compact('brand', 'todayRedemptions'));
    }

    /**
     * API JSON: Verifica si el QR/loyalty_code ingresado es válido y qué cupones tiene pendientes.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'loyalty_code' => 'required|string',
        ]);

        $brand = Auth::user()->brands()->first();
        $code = strtoupper(trim($request->loyalty_code));

        // 1. Buscar el registro por el código de cupones
        $registration = Registration::with(['attendee', 'event'])
            ->where('loyalty_code', $code)
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Código inválido. No se encontró ningún asistente con esta clave.'
            ], 404);
        }

        // 2. Buscar cupones activos de la marca
        $coupons = LoyaltyCoupon::where('brand_id', $brand->id)
            ->where('is_active', true)
            ->get();

        if ($coupons->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tu marca no tiene cupones o campañas activas actualmente.',
                'attendee_name' => $registration->attendee->full_name,
            ], 200);
        }

        $availableCoupons = [];

        foreach ($coupons as $coupon) {
            // A. Si es asignación selectiva, verificar si este registro específico fue asignado
            if ($coupon->allocation_strategy === 'selective') {
                $isAllocated = $coupon->allocations()
                    ->where('registration_id', $registration->id)
                    ->exists();
                if (!$isAllocated) {
                    continue; // Omitimos este cupón si no le pertenece al asistente
                }
            }

            // B. Contar usos del asistente específico para este cupón
            $userUses = $coupon->redemptions()
                ->where('registration_id', $registration->id)
                ->count();

            // C. Contar usos globales del cupón (si aplica)
            $globalUses = $coupon->redemptions()->count();

            $isAvailable = true;
            $statusMessage = 'Disponible para canjear';

            if ($userUses >= $coupon->usage_limit_per_attendee) {
                $isAvailable = false;
                $statusMessage = 'Límite alcanzado por este cliente (' . $userUses . '/' . $coupon->usage_limit_per_attendee . ' usos)';
            } elseif ($coupon->global_limit && $globalUses >= $coupon->global_limit) {
                $isAvailable = false;
                $statusMessage = 'Campaña agotada a nivel general.';
            }

            $availableCoupons[] = [
                'id' => $coupon->id,
                'title' => $coupon->title,
                'description' => $coupon->description,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'usage_limit' => $coupon->usage_limit_per_attendee,
                'current_uses' => $userUses,
                'validity_scope' => $coupon->validity_scope,
                'is_available' => $isAvailable,
                'status_message' => $statusMessage,
            ];
        }

        return response()->json([
            'success' => true,
            'attendee' => [
                'id' => $registration->attendee->id,
                'name' => $registration->attendee->full_name,
                'email' => $registration->attendee->email,
                'company' => $registration->attendee->company,
            ],
            'registration_id' => $registration->id,
            'coupons' => $availableCoupons
        ]);
    }

    /**
     * API JSON: Realiza el canje oficial de un cupón y lo registra en la BD con locks para evitar race conditions.
     */
    public function redeemCoupon(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'loyalty_coupon_id' => 'required|exists:loyalty_coupons,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $brand = Auth::user()->brands()->first();

        // Usar Transacción y Lock en Base de Datos para máxima seguridad ante escaneos dobles concurrentes
        try {
            $redemption = DB::transaction(function () use ($request, $brand) {
                $coupon = LoyaltyCoupon::where('id', $request->loyalty_coupon_id)
                    ->where('brand_id', $brand->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $registration = Registration::where('id', $request->registration_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Volver a evaluar límites dentro de la transacción bloqueada
                $userUses = CouponRedemption::where('loyalty_coupon_id', $coupon->id)
                    ->where('registration_id', $registration->id)
                    ->count();

                if ($userUses >= $coupon->usage_limit_per_attendee) {
                    throw new \Exception('Operación fallida: El cliente ya consumió el cupo máximo permitido para este beneficio.');
                }

                if ($coupon->global_limit) {
                    $globalUses = CouponRedemption::where('loyalty_coupon_id', $coupon->id)->count();
                    if ($globalUses >= $coupon->global_limit) {
                        throw new \Exception('Operación fallida: La campaña de cupones ha alcanzado su límite global.');
                    }
                }

                // Crear Registro de Redención
                return CouponRedemption::create([
                    'loyalty_coupon_id' => $coupon->id,
                    'registration_id' => $registration->id,
                    'processed_by_user_id' => Auth::id(),
                    'redeemed_at' => now(),
                    'notes' => $request->notes,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => '¡Canje registrado con éxito!',
                'redeemed_at' => $redemption->redeemed_at->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Cierra la sesión del usuario de marca.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('brand.login');
    }
}
