<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Permission;

return new class extends Migration
{
    public function up()
    {
        // 1. Create DataType for Custom Forms
        $formDataType = DataType::firstOrNew(['name' => 'custom_forms']);
        if (!$formDataType->exists) {
            $formDataType->fill([
                'slug'                  => 'custom-forms',
                'display_name_singular' => 'Formulario Personalizado',
                'display_name_plural'   => 'Formularios Personalizados',
                'icon'                  => 'voyager-documentation',
                'model_name'            => 'App\\Models\\CustomForm',
                'policy_name'           => null,
                'controller'            => '',
                'generate_permissions'  => 1,
                'description'           => '',
            ])->save();
        }

        // 2. Create DataRows for Custom Forms
        $this->createDataRow($formDataType->id, 'id', 'number', 'ID', 1, 0, 0, 0, 0, 0, '{}', 1);
        $this->createDataRow($formDataType->id, 'uuid', 'text', 'UUID (API ID)', 1, 1, 1, 1, 1, 0, '{"read_only":true}', 2);
        $this->createDataRow($formDataType->id, 'name', 'text', 'Nombre', 1, 1, 1, 1, 1, 1, '{"validation":{"rule":"required"}}', 3);
        $this->createDataRow($formDataType->id, 'description', 'text_area', 'Descripción', 1, 0, 1, 1, 1, 1, '{}', 4);
        $this->createDataRow($formDataType->id, 'event_id', 'relationship', 'Evento', 1, 1, 1, 1, 1, 1, '{"relationship":{"key":"id","label":"name","page_slug":"events","model":"App\\\\Models\\\\Event","type":"belongsTo","column":"event_id"}}', 5);
        $this->createDataRow($formDataType->id, 'landing_page_id', 'relationship', 'Landing Page', 1, 1, 1, 1, 1, 1, '{"relationship":{"key":"id","label":"name","page_slug":"landing-pages","model":"App\\\\Models\\\\LandingPage","type":"belongsTo","column":"landing_page_id"}}', 6);
        $this->createDataRow($formDataType->id, 'fields', 'code_editor', 'Campos (JSON Structure)', 1, 0, 1, 1, 1, 0, '{"language":"json"}', 7);
        $this->createDataRow($formDataType->id, 'created_at', 'timestamp', 'Creado', 0, 1, 1, 0, 0, 0, '{}', 8);
        $this->createDataRow($formDataType->id, 'updated_at', 'timestamp', 'Actualizado', 0, 0, 0, 0, 0, 0, '{}', 9);

        // 3. Create DataType for Form Results
        $resultDataType = DataType::firstOrNew(['name' => 'form_results']);
        if (!$resultDataType->exists) {
            $resultDataType->fill([
                'slug'                  => 'form-results',
                'display_name_singular' => 'Resultado de Formulario',
                'display_name_plural'   => 'Resultados de Formularios',
                'icon'                  => 'voyager-list',
                'model_name'            => 'App\\Models\\FormResult',
                'policy_name'           => null,
                'controller'            => '',
                'generate_permissions'  => 1,
                'description'           => '',
            ])->save();
        }

        // 4. Create DataRows for Form Results
        $this->createDataRow($resultDataType->id, 'id', 'number', 'ID', 1, 0, 0, 0, 0, 0, '{}', 1);
        $this->createDataRow($resultDataType->id, 'form_id', 'relationship', 'Formulario', 1, 1, 1, 1, 1, 1, '{"relationship":{"key":"id","label":"name","page_slug":"custom-forms","model":"App\\\\Models\\\\CustomForm","type":"belongsTo","column":"form_id"}}', 2);
        
        // Virtual Columns for Event and Landing (using relationships through CustomForm)
        // Note: Voyager doesn't support nested relationships in BREAD easily without custom accessors
        $this->createDataRow($resultDataType->id, 'event_name', 'text', 'Evento', 1, 1, 1, 0, 0, 0, '{}', 3);
        $this->createDataRow($resultDataType->id, 'landing_page_name', 'text', 'Landing Page', 1, 1, 1, 0, 0, 0, '{}', 4);
        
        $this->createDataRow($resultDataType->id, 'data', 'code_editor', 'Datos Recibidos', 1, 1, 1, 1, 1, 0, '{"language":"json"}', 5);
        $this->createDataRow($resultDataType->id, 'ip_address', 'text', 'IP Origen', 1, 1, 1, 0, 0, 0, '{}', 6);
        $this->createDataRow($resultDataType->id, 'created_at', 'timestamp', 'Fecha', 1, 1, 1, 0, 0, 0, '{}', 7);
        $this->createDataRow($resultDataType->id, 'updated_at', 'timestamp', 'Actualizado', 0, 0, 0, 0, 0, 0, '{}', 8);

        // 5. Add Menu Items
        $menu = Menu::where('name', 'admin')->first();
        if ($menu) {
            $formsMenuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Gestión Formularios',
                'url'     => '',
            ]);
            if (!$formsMenuItem->exists) {
                $formsMenuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'voyager-params',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 10,
                ])->save();
            }

            MenuItem::firstOrCreate([
                'menu_id'   => $menu->id,
                'title'     => 'Formularios',
                'url'       => '',
                'route'     => 'voyager.custom-forms.index',
                'parent_id' => $formsMenuItem->id,
            ], [
                'target'     => '_self',
                'icon_class' => 'voyager-documentation',
                'color'      => null,
                'order'      => 1,
            ]);

            MenuItem::firstOrCreate([
                'menu_id'   => $menu->id,
                'title'     => 'Resultados',
                'url'       => '',
                'route'     => 'voyager.form-results.index',
                'parent_id' => $formsMenuItem->id,
            ], [
                'target'     => '_self',
                'icon_class' => 'voyager-list',
                'color'      => null,
                'order'      => 2,
            ]);
        }

        // 6. Generate Permissions
        Permission::generateFor('custom_forms');
        Permission::generateFor('form_results');
    }

    protected function createDataRow($typeId, $field, $type, $displayName, $required, $browse, $read, $edit, $add, $delete, $details, $order)
    {
        $dataRow = DataRow::firstOrNew([
            'data_type_id' => $typeId,
            'field'        => $field,
        ]);
        $dataRow->fill([
            'type'         => $type,
            'display_name' => $displayName,
            'required'     => $required,
            'browse'       => $browse,
            'read'         => $read,
            'edit'         => $edit,
            'add'          => $add,
            'delete'       => $delete,
            'details'      => $details,
            'order'        => $order,
        ])->save();
    }

    public function down()
    {
        $customFormDataType = DataType::where('name', 'custom_forms')->first();
        if ($customFormDataType) {
            DataRow::where('data_type_id', $customFormDataType->id)->delete();
            $customFormDataType->delete();
        }

        $formResultDataType = DataType::where('name', 'form_results')->first();
        if ($formResultDataType) {
            DataRow::where('data_type_id', $formResultDataType->id)->delete();
            $formResultDataType->delete();
        }

        Permission::removeFrom('custom_forms');
        Permission::removeFrom('form_results');
    }
};
