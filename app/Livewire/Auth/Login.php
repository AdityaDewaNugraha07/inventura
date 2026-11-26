<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.guest')]
class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    public function authenticate()
    {
        $credentials = $this->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials, $this->remember)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                $this->addError('email', 'Akun kamu sedang dinonaktifkan. Hubungi Administrator.');
                $this->password = '';
                return;
            }

            session()->regenerate();
            $targetRoute = $user->getFirstAccessibleRoute();

            return redirect()->intended(route($targetRoute));
        }

        $this->addError('email', 'Email atau password yang kamu masukkan salah.');
        $this->password = '';
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}