<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$listing = App\Models\Listing::where('description', 'like', '%Barang/Jasa%')->first();
$searchable = $listing->searchable;
$term = 'barang';

$res = DB::select("SELECT to_tsvector('indonesian', ?) @@ websearch_to_tsquery('indonesian', ?) as m", [$searchable, $term]);

echo "Searchable text: " . $searchable . PHP_EOL;
echo "Match: " . ($res[0]->m ? "TRUE" : "FALSE") . PHP_EOL;



