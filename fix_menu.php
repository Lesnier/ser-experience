<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\DataRow;

echo "Fixing Permissions...\n";
Permission::generateFor('custom_forms');
Permission::generateFor('form_results');

$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    $permissions = Permission::whereIn('table_name', ['custom_forms', 'form_results'])->pluck('id')->all();
    $adminRole->permissions()->syncWithoutDetaching($permissions);
    echo "Permissions synced to Admin role.\n";
}

echo "Fixing Menu...\n";
$menu = Menu::where('name', 'admin')->first();
if ($menu) {
    $parent = MenuItem::where('menu_id', $menu->id)->where('title', 'Gestión Formularios')->first();
    if (!$parent) {
        $parent = MenuItem::create([
            'menu_id' => $menu->id,
            'title'   => 'Gestión Formularios',
            'url'     => '',
            'target'     => '_self',
            'icon_class' => 'voyager-params',
            'order'      => 10,
        ]);
        echo "Created parent menu item.\n";
    }

    MenuItem::firstOrCreate([
        'menu_id'   => $menu->id,
        'title'     => 'Formularios',
        'route'     => 'voyager.custom-forms.index',
    ], [
        'url'        => '',
        'target'     => '_self',
        'icon_class' => 'voyager-documentation',
        'parent_id'  => $parent->id,
        'order'      => 1,
    ]);

    MenuItem::firstOrCreate([
        'menu_id'   => $menu->id,
        'title'     => 'Resultados',
        'route'     => 'voyager.form-results.index',
    ], [
        'url'        => '',
        'target'     => '_self',
        'icon_class' => 'voyager-list',
        'parent_id'  => $parent->id,
        'order'      => 2,
    ]);
    echo "Created children menu items.\n";
}

echo "Adding relationship to Landing Page BREAD...\n";
$lpDataType = DataType::where('slug', 'landing-pages')->first();
if ($lpDataType) {
    DataRow::firstOrCreate([
        'data_type_id' => $lpDataType->id,
        'field'        => 'landing_page_belongsto_custom_form_relationship',
    ], [
        'type'         => 'relationship',
        'display_name' => 'Formularios Asociados',
        'required'     => 0,
        'browse'       => 0,
        'read'         => 1,
        'edit'         => 1,
        'add'          => 1,
        'delete'       => 0,
        'details'      => '{"relationship":{"key":"id","label":"name","page_slug":"custom-forms","model":"App\\\\Models\\\\CustomForm","type":"hasMany","column":"landing_page_id"}}',
        'order'        => 10,
    ]);
    echo "Added relationship to Landing Page BREAD.\n";
}

echo "Done!\n";
