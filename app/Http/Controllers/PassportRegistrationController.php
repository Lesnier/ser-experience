<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Registration;
use App\Models\TicketType;
use App\Mail\AttendeeWelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PassportRegistrationController extends Controller
{
    /**
     * Muestra el formulario rápido de registro para el pasaporte móvil.
     */
    public function showForm()
    {
        // Obtenemos el último evento activo
        $event = Event::where('status', 'active')->latest()->first();
        
        if (!$event) {
            // Si no hay eventos activos, tomamos el último creado por robustez de demostración
            $event = Event::latest()->first();
        }

        return view('passport.register_form', compact('event'));
    }

    /**
     * Procesa el registro rápido y genera las claves únicas.
     */
    public function register(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
        ]);

        // 1. Buscar o crear asistente por email
        $attendee = Attendee::updateOrCreate(
            ['email' => $request->email],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name ?? '',
                'phone' => $request->phone,
            ]
        );

        $event = Event::findOrFail($request->event_id);

        // 2. Verificar si ya está registrado en este evento
        $registration = Registration::where('event_id', $event->id)
            ->where('attendee_id', $attendee->id)
            ->first();

        if (!$registration) {
            // 3. Obtener el tipo de ticket "Regalo" o el primero disponible
            $ticketType = TicketType::where('event_id', $event->id)
                ->where(function($query) {
                    $query->where('name', 'LIKE', '%Regalo%')
                          ->orWhere('name', 'LIKE', '%General%');
                })
                ->first();

            if (!$ticketType) {
                // Si no existe, tomamos el primero por defecto del evento
                $ticketType = TicketType::where('event_id', $event->id)->first();
            }

            // Si de plano no hay tipos de ticket creados en el evento, lanzamos error
            if (!$ticketType) {
                return back()->withErrors(['error' => 'No hay tipos de invitaciones configuradas para este evento.'])->withInput();
            }

            // 4. Generar claves únicas robustas de 12 caracteres
            $entryCode = 'ENT-' . strtoupper(Str::random(8));
            $loyaltyCode = 'LOY-' . strtoupper(Str::random(8));

            // Asegurar unicidad en bucle simple
            while (Registration::where('entry_code', $entryCode)->exists()) {
                $entryCode = 'ENT-' . strtoupper(Str::random(8));
            }
            while (Registration::where('loyalty_code', $loyaltyCode)->exists()) {
                $loyaltyCode = 'LOY-' . strtoupper(Str::random(8));
            }

            // 5. Crear registro
            $registration = Registration::create([
                'event_id' => $event->id,
                'attendee_id' => $attendee->id,
                'ticket_type_id' => $ticketType->id,
                'entry_code' => $entryCode,
                'loyalty_code' => $loyaltyCode,
                'status' => 'confirmed',
            ]);

            // Decrementar stock del ticket
            $ticketType->decrement('quantity_available');
        }

        // 6. Enviar Email con el Código de Cupones
        try {
            Mail::to($attendee->email)->send(new AttendeeWelcomeMail($registration));
        } catch (\Exception $e) {
            // Log de fallos de envío, pero permitimos que continúe para no romper flujo de usuario
            \Log::error('Fallo envío email bienvenida: ' . $e->getMessage());
        }

        // Redirigir a pantalla de éxito compartiendo los códigos por sesión (solo para demo o display rápido)
        return redirect()->route('passport.success')->with([
            'attendee_name' => $attendee->full_name,
            'loyalty_code' => $registration->loyalty_code,
            'event_name' => $event->name
        ]);
    }

    /**
     * Muestra la página de éxito del registro.
     */
    public function success()
    {
        if (!session('attendee_name')) {
            return redirect()->route('passport.register');
        }
        return view('passport.success');
    }
}
