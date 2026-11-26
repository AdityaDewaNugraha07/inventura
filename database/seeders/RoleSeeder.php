<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Menu;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name'           => 'Super Administrator',
                'description'    => 'Akses penuh ke seluruh fitur, data master, transaksi, dan pengaturan sistem.',
                'is_super_admin' => true,
            ]
        );

        $staffGudang = Role::firstOrCreate(
            ['slug' => 'staff-gudang'],
            [
                'name'           => 'Staff Gudang',
                'description'    => 'Mengelola data barang serta pencatatan transaksi barang masuk dan keluar.',
                'is_super_admin' => false,
            ]
        );

        $manajer = Role::firstOrCreate(
            ['slug' => 'manajer'],
            [
                'name'           => 'Manajer',
                'description'    => 'Memantau dashboard, riwayat mutasi stok, dan mencetak laporan periode.',
                'is_super_admin' => false,
            ]
        );

        // Beri hak akses default ke Staff Gudang & Manajer berdasarkan route yang ada
        $staffMenus = Menu::whereIn('route_name', [
            'dashboard', 'barang', 'transaksi.masuk', 'transaksi.keluar', 'transaksi.index'
        ])->pluck('id')->toArray();
        $staffGudang->menus()->syncWithoutDetaching($staffMenus);

        $managerMenus = Menu::whereIn('route_name', [
            'dashboard', 'transaksi.index', 'laporan.index'
        ])->pluck('id')->toArray();
        $manajer->menus()->syncWithoutDetaching($managerMenus);

        // Otomatis tetapkan akun admin yang sudah ada menjadi Super Administrator
        User::whereNull('role_id')->update(['role_id' => $superAdmin->id]);
    }
}