<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuGroup;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // Grup 1: Menu Utama
        $group1 = MenuGroup::create(['name' => 'MENU UTAMA', 'order_no' => 1]);
        Menu::create([
            'menu_group_id' => $group1->id, 
            'name' => 'Dashboard', 
            'route_name' => 'dashboard', 
            'icon' => 'heroicon-o-home', // Nama ikon saja
            'order_no' => 1
        ]);

        // Grup 2: Data Master
        $group2 = MenuGroup::create(['name' => 'DATA MASTER', 'order_no' => 2]);
        Menu::create([
            'menu_group_id' => $group2->id, 
            'name' => 'Data Barang', 
            'route_name' => 'barang', 
            'icon' => 'heroicon-o-cube', // Nama ikon saja
            'order_no' => 1
        ]);
        
        // Grup 3: Pengaturan
        $group3 = MenuGroup::create(['name' => 'PENGATURAN', 'order_no' => 3]);
        Menu::create([
            'menu_group_id' => $group3->id, 
            'name' => 'Manajemen Menu', 
            'route_name' => 'setting.menu',
            'icon' => 'heroicon-o-cog-8-tooth', // Nama ikon saja
            'order_no' => 1
        ]);
    }
}