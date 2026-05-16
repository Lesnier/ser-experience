<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;

echo "Updating BREAD for custom_forms...\n";

$dt = DataType::where('slug', 'custom-forms')->first();
if ($dt) {
    // UUID should not be in Add or Edit (it's internal/system generated)
    DataRow::where('data_type_id', $dt->id)->where('field', 'uuid')->update([
        'add' => 0,
        'edit' => 0,
        'read' => 1,
        'browse' => 1
    ]);

    // Description should not be in Add (system generates it)
    DataRow::where('data_type_id', $dt->id)->where('field', 'description')->update([
        'add' => 0,
        'edit' => 1,
        'read' => 1,
        'browse' => 1
    ]);
    
    echo "Updated uuid and description settings.\n";
}

echo "Done.\n";
