<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;
use Illuminate\Support\Facades\DB;

class VoyagerBreadEventSystemSeeder extends Seeder
{
    public function run(): void
    {
        $this->createEventsBread();
        $this->createTicketTypesBread();
        $this->createAttendeesBread();
        $this->createRegistrationsBread();
        $this->createBrandsBread();
        $this->createLoyaltyCouponsBread();
        $this->createCouponRedemptionsBread();
        
        $this->organizeMenus();
        $this->setupBrandRepresentativeRole();
    }

    private function createDataType($name, $slug, $singular, $plural, $icon, $model, $policy = null)
    {
        $dataType = DataType::firstOrNew(['slug' => $slug]);
        if (!$dataType->exists) {
            $dataType->fill([
                'name'                  => $name,
                'display_name_singular' => $singular,
                'display_name_plural'   => $plural,
                'icon'                  => $icon,
                'model_name'            => $model,
                'policy_name'           => $policy,
                'controller'            => null,
                'generate_permissions'  => 1,
                'server_side'           => 1,
                'description'           => '',
            ])->save();
        }

        Permission::generateFor($dataType->name);
        
        // Asignar todos los permisos al admin (rol 1)
        $role = Role::where('name', 'admin')->first();
        if ($role) {
            $permissions = Permission::where('table_name', $dataType->name)->pluck('id')->all();
            $role->permissions()->syncWithoutDetaching($permissions);
        }

        return $dataType;
    }

    private function createDataRow($dataType, $field, $type, $displayName, $options = [])
    {
        $dataRow = DataRow::firstOrNew([
            'data_type_id' => $dataType->id,
            'field'        => $field,
        ]);

        if (!$dataRow->exists) {
            $dataRow->fill(array_merge([
                'type'         => $type,
                'display_name' => $displayName,
                'required'     => 0,
                'browse'       => 1,
                'read'         => 1,
                'edit'         => 1,
                'add'          => 1,
                'delete'       => 1,
                'details'      => '',
                'order'        => DataRow::where('data_type_id', $dataType->id)->max('order') + 1,
            ], $options))->save();
        }
        return $dataRow;
    }

    private function createEventsBread()
    {
        $dt = $this->createDataType('events', 'events', 'Evento', 'Eventos', 'voyager-calendar', 'App\\Models\\Event');
        
        $this->createDataRow($dt, 'id', 'number', 'ID', ['browse' => 0, 'edit' => 0, 'add' => 0]);
        $this->createDataRow($dt, 'name', 'text', 'Nombre');
        $this->createDataRow($dt, 'slug', 'text', 'URL Slug', ['details' => '{"slugify":{"origin":"name"}}']);
        $this->createDataRow($dt, 'description', 'rich_text_box', 'Descripción', ['browse' => 0]);
        $this->createDataRow($dt, 'banner_image', 'image', 'Banner', ['browse' => 0]);
        $this->createDataRow($dt, 'location_name', 'text', 'Lugar');
        $this->createDataRow($dt, 'start_date', 'timestamp', 'Fecha de Inicio');
        $this->createDataRow($dt, 'end_date', 'timestamp', 'Fecha de Fin');
        $this->createDataRow($dt, 'status', 'select_dropdown', 'Estado', ['details' => '{"default":"draft","options":{"draft":"Borrador","active":"Activo","completed":"Completado","cancelled":"Cancelado"}}']);
        $this->createDataRow($dt, 'capacity', 'number', 'Aforo Máximo', ['browse' => 0]);
        $this->createDataRow($dt, 'created_at', 'timestamp', 'Creado', ['browse' => 0, 'edit' => 0, 'add' => 0]);
    }

    private function createTicketTypesBread()
    {
        $dt = $this->createDataType('ticket_types', 'ticket-types', 'Tipo de Invitación', 'Tipos de Invitaciones', 'voyager-ticket', 'App\\Models\\TicketType');
        
        $this->createDataRow($dt, 'id', 'number', 'ID', ['browse' => 0, 'edit' => 0, 'add' => 0]);
        $this->createDataRow($dt, 'event_id', 'number', 'ID Evento', ['browse' => 0, 'edit' => 1, 'add' => 1]);
        
        // Relación BelongsTo
        $this->createDataRow($dt, 'ticket_type_belongsto_event_relationship', 'relationship', 'Evento', [
            'details' => '{"model":"App\\\\Models\\\\Event","table":"events","type":"belongsTo","column":"event_id","key":"id","label":"name","pivot_table":"events","pivot":"0","taggable":"0"}'
        ]);

        $this->createDataRow($dt, 'name', 'text', 'Nombre de Ticket');
        $this->createDataRow($dt, 'price', 'number', 'Precio ($)', ['details' => '{"step": "0.01"}']);
        $this->createDataRow($dt, 'quantity_total', 'number', 'Inventario Total');
        $this->createDataRow($dt, 'quantity_available', 'number', 'Disponibles');
        $this->createDataRow($dt, 'is_active', 'checkbox', 'Activo', ['details' => '{"on":"Sí","off":"No","checked":true}']);
    }

    private function createAttendeesBread()
    {
        $dt = $this->createDataType('attendees', 'attendees', 'Asistente', 'Asistentes', 'voyager-people', 'App\\Models\\Attendee');
        
        $this->createDataRow($dt, 'id', 'number', 'ID', ['browse' => 0, 'edit' => 0, 'add' => 0]);
        $this->createDataRow($dt, 'first_name', 'text', 'Nombres');
        $this->createDataRow($dt, 'last_name', 'text', 'Apellidos');
        $this->createDataRow($dt, 'email', 'text', 'Correo Electrónico');
        $this->createDataRow($dt, 'phone', 'text', 'Teléfono');
        $this->createDataRow($dt, 'company', 'text', 'Empresa', ['browse' => 0]);
        $this->createDataRow($dt, 'created_at', 'timestamp', 'Registrado el', ['edit' => 0, 'add' => 0]);
    }

    private function createRegistrationsBread()
    {
        $dt = $this->createDataType('registrations', 'registrations', 'Registro de Acceso', 'Registros de Acceso', 'voyager-check-circle', 'App\\Models\\Registration');
        
        $this->createDataRow($dt, 'id', 'number', 'ID', ['browse' => 0, 'edit' => 0, 'add' => 0]);
        
        $this->createDataRow($dt, 'event_id', 'number', 'Evento_ID', ['browse' => 0]);
        $this->createDataRow($dt, 'registration_belongsto_event_relationship', 'relationship', 'Evento', [
            'details' => '{"model":"App\\\\Models\\\\Event","table":"events","type":"belongsTo","column":"event_id","key":"id","label":"name","pivot_table":"events","pivot":"0","taggable":"0"}'
        ]);

        $this->createDataRow($dt, 'attendee_id', 'number', 'Asistente_ID', ['browse' => 0]);
        $this->createDataRow($dt, 'registration_belongsto_attendee_relationship', 'relationship', 'Asistente', [
            'details' => '{"model":"App\\\\Models\\\\Attendee","table":"attendees","type":"belongsTo","column":"attendee_id","key":"id","label":"email","pivot_table":"attendees","pivot":"0","taggable":"0"}'
        ]);

        $this->createDataRow($dt, 'ticket_type_id', 'number', 'Ticket_ID', ['browse' => 0]);
        $this->createDataRow($dt, 'registration_belongsto_ticket_type_relationship', 'relationship', 'Tipo de Ticket', [
            'details' => '{"model":"App\\\\Models\\\\TicketType","table":"ticket_types","type":"belongsTo","column":"ticket_type_id","key":"id","label":"name","pivot_table":"ticket_types","pivot":"0","taggable":"0"}'
        ]);

        $this->createDataRow($dt, 'entry_code', 'text', 'QR Puerta', ['edit' => 0]);
        $this->createDataRow($dt, 'loyalty_code', 'text', 'QR Beneficios', ['edit' => 0]);
        $this->createDataRow($dt, 'status', 'select_dropdown', 'Estado', ['details' => '{"default":"confirmed","options":{"pending":"Pendiente","confirmed":"Confirmado","checked_in":"Ingresó","cancelled":"Cancelado"}}']);
    }

    private function createBrandsBread()
    {
        $dt = $this->createDataType('brands', 'brands', 'Marca / Stand', 'Marcas / Stands', 'voyager-shop', 'App\\Models\\Brand');
        
        $this->createDataRow($dt, 'id', 'number', 'ID', ['browse' => 0, 'edit' => 0, 'add' => 0]);
        $this->createDataRow($dt, 'event_id', 'number', 'Evento_ID', ['browse' => 0]);
        $this->createDataRow($dt, 'brand_belongsto_event_relationship', 'relationship', 'Evento', [
            'details' => '{"model":"App\\\\Models\\\\Event","table":"events","type":"belongsTo","column":"event_id","key":"id","label":"name","pivot_table":"events","pivot":"0","taggable":"0"}'
        ]);
        $this->createDataRow($dt, 'name', 'text', 'Nombre de Marca');
        $this->createDataRow($dt, 'stand_number', 'text', 'Número de Stand');
        $this->createDataRow($dt, 'logo', 'image', 'Logo', ['browse' => 0]);
    }

    private function createLoyaltyCouponsBread()
    {
        $dt = $this->createDataType('loyalty_coupons', 'loyalty-coupons', 'Campaña / Cupón', 'Campañas / Cupones', 'voyager-gift', 'App\\Models\\LoyaltyCoupon');
        
        $this->createDataRow($dt, 'id', 'number', 'ID', ['browse' => 0, 'edit' => 0, 'add' => 0]);
        $this->createDataRow($dt, 'brand_id', 'number', 'Marca_ID', ['browse' => 0]);
        $this->createDataRow($dt, 'loyalty_coupon_belongsto_brand_relationship', 'relationship', 'Marca', [
            'details' => '{"model":"App\\\\Models\\\\Brand","table":"brands","type":"belongsTo","column":"brand_id","key":"id","label":"name","pivot_table":"brands","pivot":"0","taggable":"0"}'
        ]);
        
        $this->createDataRow($dt, 'title', 'text', 'Título del Beneficio');
        $this->createDataRow($dt, 'discount_type', 'select_dropdown', 'Tipo', ['details' => '{"default":"freebie","options":{"percentage":"Porcentaje %","fixed_amount":"Monto Fijo $","freebie":"Regalo"}}']);
        $this->createDataRow($dt, 'discount_value', 'number', 'Valor', ['details' => '{"step": "0.01"}']);
        $this->createDataRow($dt, 'usage_limit_per_attendee', 'number', 'Usos x Persona');
        $this->createDataRow($dt, 'global_limit', 'number', 'Límite Global', ['browse' => 0]);
        $this->createDataRow($dt, 'validity_scope', 'select_dropdown', 'Validez', ['details' => '{"default":"during_event","options":{"during_event":"Durante Evento","post_event":"Post-Evento","both":"Ambos"}}']);
        $this->createDataRow($dt, 'allow_brand_modification', 'checkbox', 'Marca puede editar', ['details' => '{"on":"Sí","off":"No","checked":false}']);
        $this->createDataRow($dt, 'is_active', 'checkbox', 'Activo', ['details' => '{"on":"Sí","off":"No","checked":true}']);
    }

    private function createCouponRedemptionsBread()
    {
        $dt = $this->createDataType('coupon_redemptions', 'coupon-redemptions', 'Historial de Canje', 'Historial de Canjes', 'voyager-bag', 'App\\Models\\CouponRedemption');
        
        $this->createDataRow($dt, 'id', 'number', 'ID', ['browse' => 0, 'edit' => 0, 'add' => 0]);
        
        // Solo lectura
        $this->createDataRow($dt, 'loyalty_coupon_id', 'number', 'Campaña_ID', ['browse' => 0, 'edit' => 0, 'add' => 0]);
        $this->createDataRow($dt, 'redemption_belongsto_coupon_relationship', 'relationship', 'Cupón', [
            'edit' => 0, 'add' => 0,
            'details' => '{"model":"App\\\\Models\\\\LoyaltyCoupon","table":"loyalty_coupons","type":"belongsTo","column":"loyalty_coupon_id","key":"id","label":"title","pivot_table":"loyalty_coupons","pivot":"0","taggable":"0"}'
        ]);

        $this->createDataRow($dt, 'registration_id', 'number', 'Registro_ID', ['browse' => 0, 'edit' => 0, 'add' => 0]);
        $this->createDataRow($dt, 'processed_by_user_id', 'number', 'Usuario Stand ID', ['browse' => 0, 'edit' => 0, 'add' => 0]);
        $this->createDataRow($dt, 'redeemed_at', 'timestamp', 'Fecha de Canje', ['edit' => 0, 'add' => 0]);
        $this->createDataRow($dt, 'notes', 'text', 'Notas', ['edit' => 0, 'add' => 0]);
    }

    private function organizeMenus()
    {
        $menu = Menu::where('name', 'admin')->first();
        if (!$menu) return;

        // Crear menú padre "Gestión de Eventos"
        $eventosMenu = MenuItem::firstOrCreate([
            'menu_id' => $menu->id,
            'title' => 'Gestión de Eventos',
        ], [
            'icon_class' => 'voyager-calendar',
            'url' => '',
            'target' => '_self',
            'order' => 2,
        ]);

        // Crear menú padre "Fidelización y Marcas"
        $fidelizacionMenu = MenuItem::firstOrCreate([
            'menu_id' => $menu->id,
            'title' => 'Fidelización y Marcas',
        ], [
            'icon_class' => 'voyager-gift',
            'url' => '',
            'target' => '_self',
            'order' => 3,
        ]);

        // Lista de menús a crear: [Título, Icono, Ruta (slug), Parent]
        $items = [
            ['Eventos', 'voyager-calendar', 'voyager.events.index', $eventosMenu->id],
            ['Tipos de Invitaciones', 'voyager-ticket', 'voyager.ticket-types.index', $eventosMenu->id],
            ['Asistentes', 'voyager-people', 'voyager.attendees.index', $eventosMenu->id],
            ['Registros de Acceso', 'voyager-check-circle', 'voyager.registrations.index', $eventosMenu->id],
            ['Marcas / Stands', 'voyager-shop', 'voyager.brands.index', $fidelizacionMenu->id],
            ['Campañas / Cupones', 'voyager-gift', 'voyager.loyalty-coupons.index', $fidelizacionMenu->id],
            ['Historial de Canjes', 'voyager-bag', 'voyager.coupon-redemptions.index', $fidelizacionMenu->id],
        ];

        $order = 1;
        foreach ($items as $item) {
            MenuItem::firstOrCreate([
                'menu_id' => $menu->id,
                'route'   => $item[2],
            ], [
                'title'      => $item[0],
                'icon_class' => $item[1],
                'target'     => '_self',
                'parent_id'  => $item[3],
                'order'      => $order++,
                'url'        => '',
            ]);
        }

        // Mover el panel de Gestión Masiva creado por la migración
        MenuItem::where('title', 'Gestión Masiva (UX)')->update(['parent_id' => $fidelizacionMenu->id, 'order' => 99]);
    }

    private function setupBrandRepresentativeRole()
    {
        $role = Role::firstOrCreate(['name' => 'brand_representative'], ['display_name' => 'Representante de Marca']);
        // Este rol no tiene permisos de admin panel globales, ya que navegan por el frontend de marcas
    }
}
