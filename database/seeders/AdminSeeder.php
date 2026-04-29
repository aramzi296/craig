<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@sebatam.com'],
            [
                'name' => 'Admin BatamCraig',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_admin' => \Illuminate\Support\Facades\DB::raw('TRUE'),
            ]
        );
    }
}
