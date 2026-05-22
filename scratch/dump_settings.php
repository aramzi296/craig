<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Setting;

try {
    $settings = Setting::all();
    echo "SETTINGS DUMP:\n";
    foreach ($settings as $setting) {
        echo "- {$setting->key}: {$setting->value}\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
