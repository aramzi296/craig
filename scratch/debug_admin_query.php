<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Listing;
use Illuminate\Http\Request;

// Simulate request
$request = Request::create('/admin/listings', 'GET', [
    'search' => '',
    'category_id' => '4',
    'status' => ''
]);

$query = Listing::with(['categories', 'user'])
    ->when($request->filled('search'), function($q) use ($request) {
        $search = $request->search;
        $q->where(function($sq) use ($search) {
            $sq->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
        });
    })
    ->when($request->filled('category_id'), function($q) use ($request) {
        $q->whereHas('categories', function($sq) use ($request) {
            $sq->where('categories.id', $request->category_id);
        });
    })
    ->when($request->has('status') && $request->status !== '', function($q) use ($request) {
        $q->where('is_active', (bool)$request->status);
    })
    ->latest();

echo "SQL: " . $query->toSql() . "\n";
echo "Bindings: " . json_encode($query->getBindings()) . "\n";
echo "Count: " . $query->count() . "\n";

foreach ($query->get() as $l) {
    echo "- " . $l->id . ": " . $l->title . "\n";
}
