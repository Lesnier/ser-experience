<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use TCG\Voyager\Models\DataRow;

echo "Flattening relationship details...\n";

$rows = DataRow::where('type', 'relationship')->get();
foreach ($rows as $row) {
    $details = $row->details;
    if (isset($details->relationship)) {
        echo "Flattening row {$row->id} ({$row->field})...\n";
        $newDetails = $details->relationship;
        // Copy any other top level properties if they exist
        foreach ($details as $key => $value) {
            if ($key !== 'relationship') {
                $newDetails->$key = $value;
            }
        }
        $row->details = $newDetails;
        $row->save();
        echo "Fixed.\n";
    }
}

echo "Done.\n";
