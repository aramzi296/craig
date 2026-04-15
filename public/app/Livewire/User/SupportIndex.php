<?php

namespace App\Livewire\User;

use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SupportIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $tickets = SupportTicket::with(['messages' => function($q) {
                $q->latest();
            }])
            ->where('user_id', Auth::id())
            ->latest('updated_at')
            ->paginate(10);

        return view('livewire.user.support-index', [
            'tickets' => $tickets,
        ])->layout('layouts.main');
    }
}
