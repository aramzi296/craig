<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Listing;

$count = Listing::whereHas('categories', function($q) {
    $q->where('categories.id', 4);
})->count();

echo "Count for Category 4: " . $count . "\n";

$listings = Listing::whereHas('categories', function($q) {
    $q->where('categories.id', 4);
})->get();

foreach ($listings as $l) {
    echo "- " . $l->title . "\n";
}
