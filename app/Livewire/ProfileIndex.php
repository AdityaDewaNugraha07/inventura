<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileIndex extends Component
{
    public $name = '';
    public $email = '';

    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';

    public function mount()
    {
        $user = auth()->user();
        $this->name  = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile()
    {
        $user = auth()->user();

        $this->validate([
            'name'  => 'required|string|min:2|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name'  => trim($this->name),
            'email' => trim($this->email),
        ]);

        session()->flash('message', 'Informasi profil berhasil diperbarui!');
        return $this->redirectRoute('profil', navigate: true);
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'password.confirmed'                => 'Konfirmasi password baru tidak sama.',
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('message', 'Password akun berhasil diubah!');
    }

    public function render()
    {
        return view('livewire.profile-index');
    }
}