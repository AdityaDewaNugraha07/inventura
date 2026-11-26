<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MenuGroup;
use App\Models\Menu;

class MenuManagement extends Component
{
    // State Modal Grup
    public $isGroupModalOpen = false;
    public $groupId = null;
    public $groupName = '';
    public $groupOrder = 1;

    // State Modal Menu / Submenu
    public $isMenuModalOpen = false;
    public $menuId = null;
    public $menu_group_id = '';
    public $parent_id = '';
    public $name = '';
    public $route_name = '';
    public $icon = 'heroicon-o-squares-2x2';
    public $order_no = 1;
    public $is_active = true;

    // Daftar pilihan Heroicons siap klik untuk Icon Picker
    public array $availableIcons = [
        'heroicon-o-home'                    => 'Home',
        'heroicon-o-squares-2x2'             => 'Dashboard',
        'heroicon-o-cube'                    => 'Barang',
        'heroicon-o-archive-box'             => 'Gudang',
        'heroicon-o-tag'                     => 'Kategori',
        'heroicon-o-folder'                  => 'Master',
        'heroicon-o-truck'                   => 'Supplier',
        'heroicon-o-building-storefront'     => 'Toko',
        'heroicon-o-arrows-right-left'       => 'Transaksi',
        'heroicon-o-arrow-down-tray'         => 'Masuk',
        'heroicon-o-arrow-up-tray'           => 'Keluar',
        'heroicon-o-shopping-cart'           => 'POS',
        'heroicon-o-clipboard-document-list' => 'Audit',
        'heroicon-o-document-chart-bar'      => 'Laporan',
        'heroicon-o-chart-bar'               => 'Statistik',
        'heroicon-o-users'                   => 'User',
        'heroicon-o-shield-check'            => 'Role',
        'heroicon-o-cog-8-tooth'             => 'Setting',
        'heroicon-o-wrench-screwdriver'      => 'Tools',
        'heroicon-o-banknotes'               => 'Keuangan',
        'heroicon-o-qr-code'                 => 'QR / SKU',
        'heroicon-o-bell'                    => 'Notifikasi',
    ];

    // Reset parent menu otomatis jika user mengganti grup
    public function updatedMenuGroupId()
    {
        $this->parent_id = '';
    }

    // ==================== CRUD GRUP MENU ====================

    public function openGroupModal($id = null)
    {
        $this->resetValidation();

        if ($id) {
            $group = MenuGroup::findOrFail($id);
            $this->groupId = $group->id;
            $this->groupName = $group->name;
            $this->groupOrder = $group->order_no;
        } else {
            $this->groupId = null;
            $this->groupName = '';
            $this->groupOrder = (MenuGroup::max('order_no') ?? 0) + 1;
        }

        $this->isGroupModalOpen = true;
    }

    public function closeGroupModal()
    {
        $this->isGroupModalOpen = false;
    }

    public function saveGroup()
    {
        $this->validate([
            'groupName'  => 'required|string|min:2|max:50',
            'groupOrder' => 'required|integer|min:1',
        ]);

        MenuGroup::updateOrCreate(
            ['id' => $this->groupId],
            [
                'name'     => strtoupper($this->groupName),
                'order_no' => $this->groupOrder,
            ]
        );

        $this->closeGroupModal();
        session()->flash('message', $this->groupId ? 'Grup menu berhasil diperbarui!' : 'Grup menu baru berhasil ditambahkan!');
        return $this->redirectRoute('setting.menu', navigate: true);
    }

    public function deleteGroup($id)
    {
        MenuGroup::findOrFail($id)->delete();
        session()->flash('message', 'Grup menu beserta isinya berhasil dihapus!');
        return $this->redirectRoute('setting.menu', navigate: true);
    }

    // ==================== CRUD MENU & SUBMENU ====================

    public function openMenuModal($id = null, $defaultGroupId = null, $defaultParentId = null)
    {
        $this->resetValidation();

        if ($id) {
            $menu = Menu::findOrFail($id);
            $this->menuId = $menu->id;
            $this->menu_group_id = $menu->menu_group_id;
            $this->parent_id = $menu->parent_id ?? '';
            $this->name = $menu->name;
            $this->route_name = $menu->route_name ?? '';
            $this->icon = $menu->icon ?? 'heroicon-o-squares-2x2';
            $this->order_no = $menu->order_no;
            $this->is_active = (bool) $menu->is_active;
        } else {
            $this->menuId = null;
            $this->menu_group_id = $defaultGroupId ?? (MenuGroup::orderBy('order_no')->value('id') ?? '');
            $this->parent_id = $defaultParentId ?? '';
            $this->name = '';
            $this->route_name = '';
            $this->icon = 'heroicon-o-squares-2x2';
            $this->order_no = (Menu::where('menu_group_id', $this->menu_group_id)->max('order_no') ?? 0) + 1;
            $this->is_active = true;
        }

        $this->isMenuModalOpen = true;
    }

    public function closeMenuModal()
    {
        $this->isMenuModalOpen = false;
    }

    public function selectIcon($iconName)
    {
        $this->icon = $iconName;
    }

    public function saveMenu()
    {
        $this->validate([
            'menu_group_id' => 'required|exists:menu_groups,id',
            'parent_id'     => 'nullable|exists:menus,id',
            'name'          => 'required|string|min:2|max:100',
            'route_name'    => 'nullable|string|max:100',
            'icon'          => 'required|string',
            'order_no'      => 'required|integer|min:1',
            'is_active'     => 'boolean',
        ]);

        Menu::updateOrCreate(
            ['id' => $this->menuId],
            [
                'menu_group_id' => $this->menu_group_id,
                'parent_id'     => $this->parent_id ?: null,
                'name'          => $this->name,
                'route_name'    => $this->route_name ?: null,
                'icon'          => $this->icon,
                'order_no'      => $this->order_no,
                'is_active'     => $this->is_active,
            ]
        );

        $this->closeMenuModal();
        session()->flash('message', $this->menuId ? 'Menu berhasil diperbarui!' : 'Menu baru berhasil ditambahkan!');
        return $this->redirectRoute('setting.menu', navigate: true);
    }

    public function toggleActive($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->update(['is_active' => !$menu->is_active]);
        session()->flash('message', 'Status menu "' . $menu->name . '" diperbarui!');
        return $this->redirectRoute('setting.menu', navigate: true);
    }

    public function deleteMenu($id)
    {
        Menu::findOrFail($id)->delete();
        session()->flash('message', 'Menu berhasil dihapus!');
        return $this->redirectRoute('setting.menu', navigate: true);
    }

    public function render()
    {
        $menuGroups = MenuGroup::with(['menus.children'])
            ->orderBy('order_no')
            ->get();

        // Daftar menu induk yang tersedia pada grup terpilih (untuk opsi dropdown Submenu)
        $parentOptions = Menu::where('menu_group_id', $this->menu_group_id)
            ->whereNull('parent_id')
            ->when($this->menuId, fn($q) => $q->where('id', '!=', $this->menuId))
            ->orderBy('order_no')
            ->get();

        return view('livewire.menu-management', [
            'menuGroups'    => $menuGroups,
            'parentOptions' => $parentOptions,
        ]);
    }
}