<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;

$cats = Category::whereHas('listings', function($q) {
    $q->where('is_active', true)->where(function($sq) {
        $sq->whereNull('expires_at')->orWhere('expires_at', '>', now());
    });
})->get();

echo "Count: " . $cats->count() . "\n";
foreach($cats as $cat) {
    echo $cat->name . " (ID: " . $cat->id . ")\n";
}
