<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\CustomForm::all() as $f) {
    echo "Name: " . $f->name . "\n";
    echo "UUID: " . $f->uuid . "\n\n";
}
