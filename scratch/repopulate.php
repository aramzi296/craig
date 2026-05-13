<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\Listing;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Listing::with('tags')->chunk(100, function ($listings) {
    foreach ($listings as $listing) {
        $listing->updateSearchableField();
    }
});

echo "Re-populated searchable field with slash fixes.\n";
