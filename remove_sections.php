<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// 1. Drop table
Schema::dropIfExists('landing_sections');

// 2. Delete BREAD Data
$dataType = TCG\Voyager\Models\DataType::where('name', 'landing_sections')->first();
if ($dataType) {
    TCG\Voyager\Models\DataRow::where('data_type_id', $dataType->id)->delete();
    $dataType->delete();
}

// 3. Delete Permissions
TCG\Voyager\Models\Permission::where('table_name', 'landing_sections')->delete();

// 4. Delete menu item
TCG\Voyager\Models\MenuItem::where('route', 'voyager.landing-sections.index')->delete();

echo "Landing Sections removed completely.\n";
