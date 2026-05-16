<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;

echo "Checking DataRows...\n";

$rows = DataRow::all();
foreach ($rows as $row) {
    if (is_string($row->details)) {
        echo "Row {$row->id} ({$row->field}) has string details. Decoding...\n";
        $decoded = json_decode($row->details);
        if (json_last_error() === JSON_ERROR_NONE) {
            $row->details = $decoded;
            $row->save();
            echo "Fixed.\n";
        } else {
            echo "Error decoding JSON for row {$row->id}: " . json_last_error_msg() . "\n";
        }
    }
}

echo "Done checking.\n";
