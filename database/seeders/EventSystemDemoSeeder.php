<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\Brand;
use App\Models\Attendee;
use App\Models\Registration;
use App\Models\LoyaltyCoupon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EventSystemDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. CREAR EVENTO DE DEMOSTRACIÓN
        $event = Event::create([
            'name' => 'Gran Feria de Salud y Bienestar 2026',
            'slug' => 'feria-bienestar-2026',
            'description' => 'El evento más grande de salud integral, cosmética y bienestar natural.',
            'location_name' => 'Centro de Exposiciones Metropolitano',
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(5),
            'status' => 'active',
            'capacity' => 5000
        ]);

        // 2. CREAR TIPOS DE TICKET/INVITACIÓN
        $generalTicket = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Pase General Feria',
            'description' => 'Acceso estándar para visitantes.',
            'price' => 0.00,
            'quantity_total' => 4000,
            'quantity_available' => 4000,
            'is_active' => true
        ]);

        $giftTicket = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Pase Especial de Regalos',
            'description' => 'Pase otorgado como premio con beneficios VIP.',
            'price' => 0.00,
            'quantity_total' => 500,
            'quantity_available' => 500,
            'is_active' => true
        ]);

        // 3. CREAR USUARIOS DE MARCA PARA LOGIN
        // Si ya existe admin general lo respetamos, creamos usuarios de prueba del stand
        $userNathally = User::updateOrCreate(
            ['email' => 'nathally@brand.com'],
            [
                'name' => 'Personal Nathally Wellness',
                'password' => Hash::make('password123'),
                'role_id' => 2, // Rol estándar
            ]
        );

        $userOrgani = User::updateOrCreate(
            ['email' => 'organifood@brand.com'],
            [
                'name' => 'Stand OrganiFood',
                'password' => Hash::make('password123'),
                'role_id' => 2,
            ]
        );

        // 4. CREAR MARCAS / STANDS Y VINCULAR USUARIOS
        $brandWellness = Brand::create([
            'event_id' => $event->id,
            'name' => 'Nathally Wellness Spa',
            'stand_number' => 'A-12',
            'description' => 'Especialistas en terapias capilares y cuidado de la piel.'
        ]);
        $brandWellness->users()->syncWithoutDetaching([$userNathally->id]);

        $brandFood = Brand::create([
            'event_id' => $event->id,
            'name' => 'OrganiFood Market',
            'stand_number' => 'B-05',
            'description' => 'Alimentos 100% orgánicos y jugos detox.'
        ]);
        $brandFood->users()->syncWithoutDetaching([$userOrgani->id]);

        $brandSkin = Brand::create([
            'event_id' => $event->id,
            'name' => 'PureSkin Laboratory',
            'stand_number' => 'A-15',
            'description' => 'Cosmética clínica natural de alta gama.'
        ]);

        // 5. CREAR POLÍTICAS DE CUPONES DE EJEMPLO (LÍMITES DE PRUEBA)
        // Cupón 1: Uso Único por persona (Freebie)
        LoyaltyCoupon::create([
            'brand_id' => $brandWellness->id,
            'title' => 'Diagnóstico Capilar Computarizado Gratis',
            'description' => 'Evaluación detallada de tu cuero cabelludo en nuestro stand.',
            'discount_type' => 'freebie',
            'discount_value' => 0,
            'usage_limit_per_attendee' => 1, // Exactamente 1 uso
            'allocation_strategy' => 'general',
            'validity_scope' => 'during_event',
            'is_active' => true
        ]);

        // Cupón 2: Uso Múltiple (3 veces) Post-Evento
        LoyaltyCoupon::create([
            'brand_id' => $brandWellness->id,
            'title' => '15% Descuento en Masajes Relajantes',
            'description' => 'Canjea este descuento en nuestro local principal visitándonos post-feria.',
            'discount_type' => 'percentage',
            'discount_value' => 15.00,
            'usage_limit_per_attendee' => 3, // Hasta 3 veces por cliente
            'allocation_strategy' => 'general',
            'validity_scope' => 'post_event',
            'is_active' => true
        ]);

        // Cupón 3: Cupón Limitado Global para OrganiFood
        LoyaltyCoupon::create([
            'brand_id' => $brandFood->id,
            'title' => 'Shot Detox Orgánico Gratis',
            'description' => 'Un refrescante shot de clorofila y jengibre. ¡Límite 20 unidades totales en la feria!',
            'discount_type' => 'freebie',
            'discount_value' => 0,
            'global_limit' => 20, // Se acaba cuando se registren 20 canjes globales
            'usage_limit_per_attendee' => 1,
            'allocation_strategy' => 'general',
            'validity_scope' => 'during_event',
            'is_active' => true
        ]);

        // 6. CREAR 50 ASISTENTES DE PRUEBA CON SUS PASAPORTES QR
        $nombres = ['Juan', 'Maria', 'Carlos', 'Ana', 'Luis', 'Laura', 'Pedro', 'Sofía', 'Diego', 'Camila'];
        $apellidos = ['García', 'Martínez', 'Pérez', 'Rodríguez', 'López', 'Sánchez', 'Gómez', 'Fernández', 'Díaz', 'Torres'];

        for ($i = 1; $i <= 50; $i++) {
            $first = $nombres[array_rand($nombres)];
            $last = $apellidos[array_rand($apellidos)];
            $email = strtolower($first . '.' . $last . $i . '@ejemplo.com');

            $attendee = Attendee::create([
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'phone' => '+593 9' . rand(10000000, 99999999),
                'company' => ($i % 3 == 0) ? 'Empresa Wellness ' . rand(1, 5) : null
            ]);

            // Alternar entre ticket general y de regalo
            $chosenTicket = ($i % 5 == 0) ? $giftTicket : $generalTicket;

            // Generar códigos únicos para cada uno
            $entryCode = 'ENT-' . strtoupper(Str::random(8));
            $loyaltyCode = 'LOY-' . strtoupper(Str::random(8));

            Registration::create([
                'event_id' => $event->id,
                'attendee_id' => $attendee->id,
                'ticket_type_id' => $chosenTicket->id,
                'entry_code' => $entryCode,
                'loyalty_code' => $loyaltyCode,
                'status' => 'confirmed',
            ]);

            $chosenTicket->decrement('quantity_available');
        }
    }
}
