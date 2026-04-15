<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        // Statistik Member
        $stats = [
            'total'     => Listing::where('user_id', $user->id)->count(),
            'active'    => Listing::where('user_id', $user->id)->where('is_active', true)->count(),
            'pending'   => Listing::where('user_id', $user->id)->where('is_active', false)->count(),
            'is_verified' => (bool)$user->is_active,
        ];

        return view('livewire.user.dashboard', compact('stats'))
            ->layout('layouts.main');
    }
}
