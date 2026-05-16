<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;

$slugs = ['custom-forms', 'form-results', 'landing-pages', 'users'];

foreach ($slugs as $slug) {
    echo "--- $slug ---\n";
    $dt = DataType::where('slug', $slug)->first();
    if (!$dt) continue;
    $rows = DataRow::where('data_type_id', $dt->id)->get();
    foreach ($rows as $row) {
        echo "Field: {$row->field}, Type: {$row->type}, Details: " . json_encode($row->details) . "\n";
    }
}
