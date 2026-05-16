<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Registration;
use App\Models\TicketType;
use App\Models\LoyaltyCoupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminToolsController extends Controller
{
    /**
     * Muestra la interfaz de herramientas operativas masivas para los organizadores.
     */
    public function showDashboard()
    {
        $events = Event::latest()->get();
        return view('admin.tools.dashboard', compact('events'));
    }

    /**
     * Generador de Cupones en Lote: Crea el mismo cupón base para todas las marcas de un evento.
     */
    public function batchCreateCoupons(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'title' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed_amount,freebie',
            'discount_value' => 'required|numeric|min:0',
            'usage_limit' => 'required|integer|min:1',
            'validity_scope' => 'required|in:during_event,post_event,both',
            'allow_brand_modification' => 'nullable|boolean'
        ]);

        $brands = Brand::where('event_id', $request->event_id)->get();

        if ($brands->isEmpty()) {
            return back()->with('error', 'Este evento no tiene marcas asociadas actualmente. Crea o importa marcas primero.');
        }

        $allowMod = $request->has('allow_brand_modification') ? true : false;
        $count = 0;

        DB::transaction(function() use ($brands, $request, $allowMod, &$count) {
            foreach ($brands as $brand) {
                LoyaltyCoupon::create([
                    'brand_id' => $brand->id,
                    'title' => $request->title,
                    'description' => $request->description ?? 'Cupón base creado automáticamente por la organización.',
                    'discount_type' => $request->discount_type,
                    'discount_value' => $request->discount_value,
                    'usage_limit_per_attendee' => $request->usage_limit,
                    'global_limit' => $request->global_limit, // Opcional
                    'allocation_strategy' => 'general',
                    'validity_scope' => $request->validity_scope,
                    'allow_brand_modification' => $allowMod,
                    'is_active' => true,
                ]);
                $count++;
            }
        });

        return back()->with('success', "¡Operación masiva exitosa! Se han replicado y activado {$count} cupones base en todas las marcas del evento.");
    }

    /**
     * Importador de Marcas: Lee CSV, crea marca, crea usuario y vincula.
     */
    public function importBrands(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $event = Event::findOrFail($request->event_id);
        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();
        
        // Obtener el rol de voyager para representantes de marca.
        // Si no existe, usamos 2 ('user') por defecto.
        $defaultRoleId = \TCG\Voyager\Models\Role::where('name', 'brand_representative')->value('id') ?? 2;

        $handle = fopen($filePath, 'r');
        
        // Detectar cabeceras
        $header = fgetcsv($handle, 1000, ","); 
        // En español e Excel a veces usan punto y coma
        if (count($header) == 1) {
            rewind($handle);
            $header = fgetcsv($handle, 1000, ";");
            $delimiter = ";";
        } else {
            $delimiter = ",";
        }

        $importedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (count($data) < 2) continue; // Fila vacía o incompleta

                // Esperamos formato: NombreMarca, StandNumero, EmailContacto
                $brandName = trim($data[0]);
                $standNumber = trim($data[1] ?? '');
                $email = trim($data[2] ?? '');

                if (empty($brandName) || empty($email)) {
                    continue;
                }

                // 1. Crear o buscar usuario
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $tempPass = 'Pass-' . strtoupper(Str::random(6));
                    $user = User::create([
                        'name' => "Personal {$brandName}",
                        'email' => $email,
                        'password' => Hash::make($tempPass),
                        'role_id' => $defaultRoleId, // Asignar rol estándar
                    ]);
                    
                    // Almacenamos contraseñas temporales en log o sesión para descargarlas al final
                    session()->push('temp_credentials', [
                        'brand' => $brandName,
                        'email' => $email,
                        'temp_pass' => $tempPass
                    ]);
                }

                // 2. Crear Marca
                $brand = Brand::create([
                    'event_id' => $event->id,
                    'name' => $brandName,
                    'stand_number' => $standNumber,
                ]);

                // 3. Vincular a través de la tabla pivote
                $brand->users()->attach($user->id);
                $importedCount++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'Ocurrió un error procesando el archivo CSV: ' . $e->getMessage());
        }

        fclose($handle);

        return back()->with('success', "¡Importación Finalizada! Se crearon {$importedCount} marcas y cuentas de acceso asociadas. Revisa abajo las claves temporales generadas.");
    }

    /**
     * Importador de Asistentes: Lee CSV, crea asistente y emite sus 2 códigos QR.
     */
    public function importAttendees(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $event = Event::findOrFail($request->event_id);
        $ticketType = TicketType::where('event_id', $event->id)->first();

        if (!$ticketType) {
            return back()->with('error', 'Debes tener configurado al menos un tipo de ticket/invitación en este evento para asociar a los asistentes.');
        }

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Autodetección de delimitador
        $header = fgetcsv($handle, 1000, ",");
        $delimiter = (count($header) == 1) ? ";" : ",";
        if ($delimiter == ";") rewind($handle); // Reiniciar cursor si leímos mal la cabecera

        $importedCount = 0;

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (count($data) < 2) continue;

                // Formato esperado: Nombres, Apellidos, Correo, Telefono
                $firstName = trim($data[0]);
                $lastName = trim($data[1] ?? '');
                $email = trim($data[2] ?? '');
                $phone = trim($data[3] ?? '');

                if (empty($firstName) || empty($email)) continue;

                // 1. Crear asistente si no existe
                $attendee = Attendee::updateOrCreate(
                    ['email' => $email],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $phone
                    ]
                );

                // 2. Verificar si ya tiene registro
                $exists = Registration::where('event_id', $event->id)
                    ->where('attendee_id', $attendee->id)
                    ->exists();

                if (!$exists) {
                    // 3. Generación de códigos únicos robustos
                    $entryCode = 'ENT-' . strtoupper(Str::random(8));
                    $loyaltyCode = 'LOY-' . strtoupper(Str::random(8));

                    while (Registration::where('entry_code', $entryCode)->exists()) {
                        $entryCode = 'ENT-' . strtoupper(Str::random(8));
                    }
                    while (Registration::where('loyalty_code', $loyaltyCode)->exists()) {
                        $loyaltyCode = 'LOY-' . strtoupper(Str::random(8));
                    }

                    // 4. Guardar Registro
                    Registration::create([
                        'event_id' => $event->id,
                        'attendee_id' => $attendee->id,
                        'ticket_type_id' => $ticketType->id,
                        'entry_code' => $entryCode,
                        'loyalty_code' => $loyaltyCode,
                        'status' => 'confirmed'
                    ]);

                    $importedCount++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'Error en la importación masiva: ' . $e->getMessage());
        }

        fclose($handle);

        return back()->with('success', "¡Éxito! Se han importado {$importedCount} asistentes nuevos y se generaron sus claves QR únicas correspondientes.");
    }
}
