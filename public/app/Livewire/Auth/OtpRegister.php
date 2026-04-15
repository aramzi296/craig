<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class OtpRegister extends Component
{
    public function render()
    {
        return view('livewire.auth.otp-register')->layout('layouts.main');
    }
}
