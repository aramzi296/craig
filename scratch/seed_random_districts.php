<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Listing;
use App\Models\District;

$districts = District::pluck('id')->toArray();

if (empty($districts)) {
    die("No districts found in database. Please seed districts first.\n");
}

$listings = Listing::whereNull('district_id')->get();
$count = 0;
foreach ($listings as $listing) {
    $listing->update(['district_id' => $districts[array_rand($districts)]]);
    $count++;
}

echo "Successfully updated " . $count . " listings with random district_id.\n";
