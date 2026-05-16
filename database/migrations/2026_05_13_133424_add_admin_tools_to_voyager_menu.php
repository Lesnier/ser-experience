<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar elemento de menú en el menú de administrador de Voyager (menu_id = 1)
        DB::table('menu_items')->insert([
            'menu_id' => 1,
            'title' => 'Gestión Masiva (UX)',
            'url' => '/admin-tools',
            'target' => '_self',
            'icon_class' => 'voyager-params',
            'color' => '#c084fc',
            'parent_id' => null,
            'order' => 10,
            'route' => null,
            'parameters' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menu_items')
            ->where('url', '/admin-tools')
            ->delete();
    }
};
