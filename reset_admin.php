<?php
use Illuminate\Support\Facades\Hash;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::where('email', 'admin@sebatam.com')->first();
if ($user) {
    $user->password = Hash::make('password');
    $user->save();
    echo "Password for admin@sebatam.com has been reset to 'password'.\n";
} else {
    echo "User admin@sebatam.com not found.\n";
}
