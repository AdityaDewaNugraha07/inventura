<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->role?->is_super_admin;
    }

    public function hasMenuAccess(string $routeName): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->role) {
            return false;
        }

        return $this->role->menus()
            ->where('is_active', true)
            ->where('route_name', $routeName)
            ->exists();
    }

    public function getFirstAccessibleRoute(): string
    {
        if ($this->isSuperAdmin()) {
            return 'dashboard';
        }

        $firstMenu = $this->role?->menus()
            ->where('is_active', true)
            ->whereNotNull('route_name')
            ->orderBy('order_no')
            ->first();

        if ($firstMenu && Route::has($firstMenu->route_name)) {
            return $firstMenu->route_name;
        }

        return 'profil';
    }
}