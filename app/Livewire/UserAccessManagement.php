<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Role;
use App\Models\Menu;
use App\Models\MenuGroup;

class UserAccessManagement extends Component
{
    use WithPagination;

    // Navigasi 2 Tab: 'users' | 'roles'
    public $activeTab = 'users';

    // Filter & Search Tab User
    public $search = '';
    public $filterRole = '';

    // State Modal User
    public $isUserModalOpen = false;
    public $userId = null;
    public $name = '';
    public $email = '';
    public $role_id = '';
    public $password = '';
    public $password_confirmation = '';
    public $is_active = true;

    // State Modal Role
    public $isRoleModalOpen = false;
    public $roleId = null;
    public $roleName = '';
    public $roleDescription = '';

    // State Modal Atur Hak Akses Menu per Role
    public $isPermissionModalOpen = false;
    public $permissionRoleId = null;
    public $permissionRoleName = '';
    public $selectedMenuIds = [];

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterRole()
    {
        $this->resetPage();
    }

    // ==================== TAB 1: CRUD USER ====================

    public function openUserModal($id = null)
    {
        $this->resetValidation();

        if ($id) {
            $user = User::findOrFail($id);
            $this->userId                = $user->id;
            $this->name                  = $user->name;
            $this->email                 = $user->email;
            $this->role_id               = $user->role_id ?? '';
            $this->is_active             = (bool) $user->is_active;
            $this->password              = '';
            $this->password_confirmation = '';
        } else {
            $this->userId                = null;
            $this->name                  = '';
            $this->email                 = '';
            $this->role_id               = Role::where('is_super_admin', false)->value('id') ?? Role::value('id') ?? '';
            $this->is_active             = true;
            $this->password              = '';
            $this->password_confirmation = '';
        }

        $this->isUserModalOpen = true;
    }

    public function saveUser()
    {
        $rules = [
            'name'      => 'required|string|min:2|max:100',
            'email'     => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($this->userId)],
            'role_id'   => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ];

        // Password wajib saat tambah user baru, opsional saat edit
        if ($this->userId) {
            $rules['password'] = 'nullable|string|min:6|confirmed';
        } else {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $this->validate($rules, [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Cegah admin menonaktifkan akunnya sendiri
        if ($this->userId && $this->userId === auth()->id() && !$this->is_active) {
            $this->addError('is_active', 'Kamu tidak dapat menonaktifkan akun yang sedang digunakan login.');
            return;
        }

        $isEdit = (bool) $this->userId;

        $data = [
            'name'      => trim($this->name),
            'email'     => trim($this->email),
            'role_id'   => $this->role_id,
            'is_active' => $this->is_active,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        User::updateOrCreate(['id' => $this->userId], $data);

        $this->isUserModalOpen = false;
        session()->flash('message', $isEdit ? 'Data user berhasil diperbarui!' : 'Akun user baru berhasil dibuat!');
    }

    public function toggleUserStatus($id)
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Tidak dapat menonaktifkan akunmu sendiri yang sedang login!');
            return;
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('message', "Akun {$user->name} berhasil {$status}!");
    }

    public function deleteUser($id)
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Kamu tidak dapat menghapus akun yang sedang dipakai login!');
            return;
        }

        User::findOrFail($id)->delete();
        session()->flash('message', 'Akun user berhasil dihapus!');
    }

    // ==================== TAB 2: CRUD ROLE ====================

    public function openRoleModal($id = null)
    {
        $this->resetValidation();

        if ($id) {
            $role = Role::findOrFail($id);
            $this->roleId          = $role->id;
            $this->roleName        = $role->name;
            $this->roleDescription = $role->description ?? '';
        } else {
            $this->roleId          = null;
            $this->roleName        = '';
            $this->roleDescription = '';
        }

        $this->isRoleModalOpen = true;
    }

    public function saveRole()
    {
        $this->validate([
            'roleName'        => ['required', 'string', 'min:2', 'max:80', Rule::unique('roles', 'name')->ignore($this->roleId)],
            'roleDescription' => 'nullable|string|max:255',
        ]);

        $isEdit = (bool) $this->roleId;
        $slug = Str::slug($this->roleName);

        // Pastikan slug unik jika nama mirip
        $baseSlug = $slug;
        $counter = 1;
        while (Role::where('slug', $slug)->when($this->roleId, fn($q) => $q->where('id', '!=', $this->roleId))->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $payload = [
            'name'        => trim($this->roleName),
            'slug'        => $slug,
            'description' => trim($this->roleDescription) ?: null,
        ];

        // Jangan ubah slug untuk role super admin bawaan
        $existing = $this->roleId ? Role::find($this->roleId) : null;
        if (!$existing || !$existing->is_super_admin) {
            $payload['slug'] = $slug;
        }

        Role::updateOrCreate(['id' => $this->roleId], $payload);

        $this->isRoleModalOpen = false;
        session()->flash('message', $isEdit ? 'Role berhasil diperbarui!' : 'Role baru berhasil ditambahkan!');
    }

    public function deleteRole($id)
    {
        $role = Role::withCount('users')->findOrFail($id);

        if ($role->is_super_admin) {
            session()->flash('error', 'Role Super Administrator tidak dapat dihapus!');
            return;
        }

        if ($role->users_count > 0) {
            session()->flash('error', "Role '{$role->name}' masih digunakan oleh {$role->users_count} user. Pindahkan role user terlebih dahulu.");
            return;
        }

        $role->delete();
        session()->flash('message', "Role '{$role->name}' berhasil dihapus!");
    }

    // ==================== ATUR HAK AKSES MENU PER ROLE ====================

    public function openPermissionModal($roleId)
    {
        $role = Role::with('menus')->findOrFail($roleId);

        if ($role->is_super_admin) {
            session()->flash('message', 'Super Administrator otomatis memiliki akses penuh ke seluruh menu.');
            return;
        }

        $this->permissionRoleId   = $role->id;
        $this->permissionRoleName = $role->name;
        $this->selectedMenuIds    = $role->menus->pluck('id')->map(fn($id) => (string) $id)->toArray();

        $this->isPermissionModalOpen = true;
    }

    public function selectAllMenus()
    {
        // Ambil semua menu aktif yang punya route (menu tunggal & submenu)
        $this->selectedMenuIds = Menu::where('is_active', true)
            ->whereNotNull('route_name')
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    public function clearAllMenus()
    {
        $this->selectedMenuIds = [];
    }

    public function savePermissions()
    {
        $role = Role::findOrFail($this->permissionRoleId);

        if (!$role->is_super_admin) {
            $menuIds = array_map('intval', $this->selectedMenuIds);
            $role->menus()->sync($menuIds);
        }

        $this->isPermissionModalOpen = false;
        session()->flash('message', "Hak akses menu untuk role '{$role->name}' berhasil disimpan!");
    }

    public function render()
    {
        $keyword = trim($this->search);

        $users = User::with('role')
            ->when($this->filterRole !== '', fn($q) => $q->where('role_id', $this->filterRole))
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%')
                      ->orWhere('email', 'like', '%' . $keyword . '%');
                });
            })
            ->latest()
            ->paginate(10);

        $roles = Role::withCount(['users', 'menus'])
            ->orderByDesc('is_super_admin')
            ->orderBy('name')
            ->get();

        $menuGroups = MenuGroup::with([
            'menus' => function ($query) {
                $query->where('is_active', true)
                      ->whereNull('parent_id')
                      ->orderBy('order_no')
                      ->with(['children' => function ($q) {
                          $q->where('is_active', true)->orderBy('order_no');
                      }]);
            }
        ])->orderBy('order_no')->get();

        return view('livewire.user-access-management', [
            'users'      => $users,
            'roles'      => $roles,
            'menuGroups' => $menuGroups,
        ]);
    }
}