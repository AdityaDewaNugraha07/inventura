<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Inventura - Inventory Management' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-screen overflow-hidden bg-gradient-to-br from-slate-950 via-teal-950 to-slate-950 text-slate-200 antialiased selection:bg-teal-500/30 selection:text-teal-200">

    @php
        $navUser = auth()->user();
        $isSuperAdmin = $navUser && $navUser->isSuperAdmin();
        $allowedMenuIds = $isSuperAdmin
            ? []
            : ($navUser?->role?->menus()->pluck('menus.id')->toArray() ?? []);

        $menuGroups = \App\Models\MenuGroup::with([
            'menus' => function ($query) {
                $query->where('is_active', true)
                      ->whereNull('parent_id')
                      ->orderBy('order_no')
                      ->with(['children' => function ($q) {
                          $q->where('is_active', true)->orderBy('order_no');
                      }]);
            }
        ])->orderBy('order_no')->get();

        if (!$isSuperAdmin) {
            $menuGroups = $menuGroups->map(function ($group) use ($allowedMenuIds) {
                $filteredMenus = $group->menus->map(function ($menu) use ($allowedMenuIds) {
                    if ($menu->children->count() > 0) {
                        $allowedChildren = $menu->children->filter(
                            fn($child) => in_array($child->id, $allowedMenuIds)
                        )->values();

                        $menu->setRelation('children', $allowedChildren);
                        return $allowedChildren->count() > 0 ? $menu : null;
                    }

                    return in_array($menu->id, $allowedMenuIds) ? $menu : null;
                })->filter()->values();

                $group->setRelation('menus', $filteredMenus);
                return $group;
            })->filter(fn($group) => $group->menus->count() > 0)->values();
        }

        // Data untuk Notifikasi Lonceng & Profil
        $lowStockItems = \App\Models\Product::where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->get();
        $lowStockCount = $lowStockItems->count();
        $currentUser = $navUser;

        // Pilih maksimal 4 menu utama untuk Mobile/Tablet Glassy Navigation Tabs sesuai hak akses
        $mobileQuickTabs = collect([
            ['label' => 'Home',      'route' => 'dashboard',       'icon' => 'heroicon-o-home'],
            ['label' => 'Barang',    'route' => 'barang',          'icon' => 'heroicon-o-cube'],
            ['label' => 'Mutasi',    'route' => 'transaksi.index', 'icon' => 'heroicon-o-arrows-right-left'],
            ['label' => 'Laporan',   'route' => 'laporan.index',   'icon' => 'heroicon-o-document-chart-bar'],
        ])->filter(function ($tab) use ($navUser) {
            return Route::has($tab['route']) && $navUser && $navUser->hasMenuAccess($tab['route']);
        })->values();
    @endphp

    <div
        x-data="{
            collapsed: localStorage.getItem('inventura_sidebar_collapsed') === 'true',
            mobileOpen: false,
            tooltipText: '',
            tooltipTop: 0,
            tooltipShow: false,
            toggleDesktop() {
                this.collapsed = !this.collapsed;
                localStorage.setItem('inventura_sidebar_collapsed', this.collapsed);
                this.tooltipShow = false;
            },
            showTip(el, text) {
                if (!this.collapsed || window.innerWidth < 1024) return;
                const rect = el.getBoundingClientRect();
                this.tooltipText = text;
                this.tooltipTop = rect.top + (rect.height / 2);
                this.tooltipShow = true;
            },
            hideTip() {
                this.tooltipShow = false;
            }
        }"
        @toggle-mobile-sidebar.window="mobileOpen = !mobileOpen"
        @keydown.escape.window="mobileOpen = false"
        class="flex h-screen w-full overflow-hidden relative"
    >

        <!-- ==================== BACKDROP DRAWER MOBILE/TABLET ==================== -->
        <div x-show="mobileOpen"
             x-cloak
             @click="mobileOpen = false"
             x-transition:enter="transition-opacity duration-300 liquid-ease"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-200 ease-in"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-950/65 backdrop-blur-md lg:hidden">
        </div>

        <!-- ==================== FLOATING TOOLTIP (SAAT SIDEBAR MINI) ==================== -->
        <div x-show="tooltipShow && collapsed"
             x-cloak
             x-transition:enter="transition duration-150 liquid-spring"
             x-transition:enter-start="opacity-0 -translate-x-2 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition duration-100 ease-in"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             :style="`top: ${tooltipTop}px; left: 108px;`"
             class="hidden lg:block fixed z-50 -translate-y-1/2 pointer-events-none px-3.5 py-1.5 rounded-xl bg-slate-900/95 border border-teal-500/30 text-teal-200 text-xs font-semibold shadow-[0_10px_30px_rgba(0,0,0,0.5)] backdrop-blur-xl whitespace-nowrap">
            <span x-text="tooltipText"></span>
        </div>

        <!-- ==================== LIQUID COLLAPSIBLE SIDEBAR ==================== -->
        <aside
            :class="[
                mobileOpen ? 'translate-x-0 opacity-100' : '-translate-x-[115%] opacity-0 lg:opacity-100 lg:translate-x-0',
                collapsed ? 'lg:w-[88px]' : 'lg:w-72'
            ]"
            class="fixed lg:static inset-y-0 left-0 z-50 w-72 shrink-0 m-3 lg:my-4 lg:ml-4 lg:mr-0 rounded-3xl bg-slate-900/85 lg:bg-white/5 backdrop-blur-2xl border border-white/10 shadow-2xl flex flex-col overflow-hidden transition-[width,transform,opacity] duration-500 liquid-ease will-change-[width,transform]"
        >
            <!-- Logo Brand Header -->
            <div class="h-20 px-4 border-b border-white/10 flex items-center justify-between shrink-0">
                <a href="{{ route('dashboard') }}" wire:navigate @click="mobileOpen = false"
                    class="flex items-center gap-3.5 min-w-0 overflow-hidden group"
                    :class="collapsed ? 'lg:mx-auto lg:gap-0' : ''">

                    <!-- Custom Geometric Cube SVG Mark -->
                    <div class="relative w-11 h-11 rounded-2xl bg-gradient-to-br from-teal-400 via-teal-500 to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-teal-500/30 border border-white/25 shrink-0 transition-transform duration-300 group-hover:scale-105">
                        <svg class="w-6 h-6 drop-shadow-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2.5L20 7v10l-8 4.5L4 17V7l8-4.5z" fill="currentColor" fill-opacity="0.16" />
                            <path d="M12 11.5L20 7" />
                            <path d="M12 11.5L4 7" />
                            <path d="M12 11.5v10" />
                        </svg>
                    </div>

                    <!-- Brand Name + Micro Subtitle -->
                    <div
                        :class="collapsed ? 'lg:w-0 lg:opacity-0 lg:pointer-events-none' : 'lg:w-auto lg:opacity-100'"
                        class="flex flex-col whitespace-nowrap overflow-hidden transition-all duration-300 liquid-ease">
                        <span class="text-xl font-extrabold text-white tracking-tight leading-none">
                            Inventura<span class="text-teal-400">.</span>
                        </span>
                        <span class="text-[10px] font-semibold uppercase tracking-[0.22em] text-teal-400/80 mt-2 leading-none">
                            Asset &amp; Stock
                        </span>
                    </div>
                </a>

                <!-- Tombol Tutup khusus Mobile/Tablet -->
                <button type="button" @click="mobileOpen = false"
                    class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <!-- Menu Area -->
            <nav class="flex-1 space-y-6 px-3 py-5 overflow-y-auto overflow-x-hidden">
                @foreach($menuGroups as $group)
                    @if($group->menus->count() > 0)
                        <div>
                            <!-- Judul Grup (berubah jadi garis pemisah minimalis saat mini) -->
                            <div class="px-3 mb-2.5 h-4 flex items-center">
                                <h3
                                    :class="collapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'"
                                    class="text-xs font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap transition-opacity duration-300">
                                    {{ $group->name }}
                                </h3>
                                <div
                                    :class="collapsed ? 'lg:block' : 'lg:hidden'"
                                    class="hidden w-6 h-px bg-white/15 mx-auto rounded-full">
                                </div>
                            </div>

                            <!-- Daftar Menu -->
                            <div class="space-y-1.5">
                                @foreach($group->menus as $menu)
                                    @if($menu->children->count() > 0)
                                        @php
                                            $hasActiveChild = $menu->children->contains(fn($c) => $c->route_name && request()->routeIs($c->route_name));
                                        @endphp
                                        <div x-data="{ open: {{ $hasActiveChild ? 'true' : 'false' }} }">
                                            <button type="button"
                                                @click="if (collapsed && window.innerWidth >= 1024) { toggleDesktop(); open = true; } else { open = !open; }"
                                                @mouseenter="showTip($el, @js($menu->name))"
                                                @mouseleave="hideTip()"
                                                :class="collapsed ? 'lg:justify-center lg:px-0' : 'justify-between px-3.5'"
                                                class="liquid-hover-sheen group relative w-full flex items-center py-2.5 rounded-2xl transition-all duration-300 liquid-ease cursor-pointer {{ $hasActiveChild ? 'bg-teal-500/15 text-teal-300 border border-teal-500/30 shadow-[0_0_20px_rgba(20,184,166,0.12)]' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">

                                                <div class="flex items-center min-w-0" :class="collapsed ? 'lg:gap-0' : 'gap-3'">
                                                    @if($menu->icon)
                                                        <x-dynamic-component :component="$menu->icon"
                                                            class="w-5 h-5 shrink-0 transition-transform duration-300 liquid-spring group-hover:scale-110 {{ $hasActiveChild ? 'text-teal-400 icon-liquid-active' : 'text-slate-400 group-hover:text-teal-300' }}" />
                                                    @endif
                                                    <span
                                                        :class="collapsed ? 'lg:w-0 lg:opacity-0 lg:overflow-hidden' : 'lg:w-auto lg:opacity-100'"
                                                        class="font-medium whitespace-nowrap transition-all duration-300 liquid-ease">
                                                        {{ $menu->name }}
                                                    </span>
                                                </div>

                                                <x-heroicon-o-chevron-down
                                                    class="w-4 h-4 shrink-0 transition-all duration-300 liquid-ease"
                                                    ::class="[open ? 'rotate-180 text-teal-400' : '', collapsed ? 'lg:hidden' : '']" />
                                            </button>

                                            <!-- Submenu Accordion (otomatis tersembunyi saat mini) -->
                                            <div x-show="open && (!collapsed || window.innerWidth < 1024)"
                                                 x-transition:enter="transition ease-out duration-200 liquid-ease"
                                                 x-transition:enter-start="opacity-0 -translate-y-1.5"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 -translate-y-1.5"
                                                 class="mt-1.5 ml-5 pl-3 border-l border-white/10 space-y-1">
                                                @foreach($menu->children as $child)
                                                    @php
                                                        $childActive = $child->route_name && request()->routeIs($child->route_name);
                                                        $hasValidRoute = $child->route_name && Route::has($child->route_name);
                                                        $childUrl = $hasValidRoute ? route($child->route_name) : '#';
                                                    @endphp
                                                    <a href="{{ $childUrl }}"
                                                       @if($hasValidRoute) wire:navigate @endif
                                                       @click="mobileOpen = false"
                                                       class="group flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm transition-all duration-200 liquid-ease {{ $childActive ? 'bg-teal-500/20 text-teal-300 font-semibold shadow-[0_0_12px_rgba(20,184,166,0.15)]' : 'text-slate-400 hover:text-white hover:bg-white/5 hover:translate-x-1' }}">
                                                        @if($child->icon)
                                                            <x-dynamic-component :component="$child->icon" class="w-4 h-4 shrink-0 {{ $childActive ? 'icon-liquid-active' : '' }}" />
                                                        @endif
                                                        <span class="truncate">{{ $child->name }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $isActive = $menu->route_name && request()->routeIs($menu->route_name);
                                            $hasValidRoute = $menu->route_name && Route::has($menu->route_name);
                                            $routeUrl = $hasValidRoute ? route($menu->route_name) : '#';
                                        @endphp
                                        <a href="{{ $routeUrl }}"
                                           @if($hasValidRoute) wire:navigate @endif
                                           @click="mobileOpen = false"
                                           @mouseenter="showTip($el, @js($menu->name))"
                                           @mouseleave="hideTip()"
                                           :class="collapsed ? 'lg:justify-center lg:px-0 lg:gap-0' : 'px-3.5 gap-3'"
                                           class="liquid-hover-sheen group relative flex items-center py-2.5 rounded-2xl transition-all duration-300 liquid-ease active:scale-[0.97] {{ $isActive ? 'bg-gradient-to-r from-teal-500/25 to-emerald-500/10 text-teal-300 border border-teal-500/30 shadow-[0_0_20px_rgba(20,184,166,0.18)]' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                                            @if($isActive)
                                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 rounded-r-full bg-teal-400 shadow-[0_0_10px_#2dd4bf]"></span>
                                            @endif
                                            @if($menu->icon)
                                                <x-dynamic-component :component="$menu->icon"
                                                    class="w-5 h-5 shrink-0 transition-transform duration-300 liquid-spring group-hover:scale-110 {{ $isActive ? 'text-teal-400 icon-liquid-active' : 'text-slate-400 group-hover:text-teal-300' }}" />
                                            @endif
                                            <span
                                                :class="collapsed ? 'lg:w-0 lg:opacity-0 lg:overflow-hidden' : 'lg:w-auto lg:opacity-100'"
                                                class="font-medium whitespace-nowrap transition-all duration-300 liquid-ease">
                                                {{ $menu->name }}
                                            </span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>

            <!-- Footer Mini Toggle (Desktop) -->
            <div class="hidden lg:flex p-3 border-t border-white/10 justify-center shrink-0">
                <button type="button" @click="toggleDesktop()"
                    class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-2xl bg-white/[0.03] hover:bg-teal-500/15 border border-white/5 hover:border-teal-500/30 text-slate-400 hover:text-teal-300 text-xs font-medium transition-all duration-300 liquid-ease cursor-pointer"
                    :title="collapsed ? 'Lebarkan Sidebar' : 'Kecilkan Sidebar'">
                    <x-heroicon-o-chevron-double-left class="w-4 h-4 shrink-0 transition-transform duration-500 liquid-spring"
                        ::class="collapsed ? 'rotate-180' : ''" />
                    <span
                        :class="collapsed ? 'w-0 opacity-0 overflow-hidden' : 'w-auto opacity-100'"
                        class="whitespace-nowrap transition-all duration-300">
                        Kecilkan Menu
                    </span>
                </button>
            </div>
        </aside>

        <!-- ==================== MAIN CONTENT AREA (min-w-0 anti layout rusak) ==================== -->
        <main class="flex-1 min-w-0 flex flex-col h-screen overflow-y-auto overflow-x-hidden">

            <!-- FLOATING NAVBAR -->
            <header class="sticky top-3 lg:top-4 z-30 shrink-0 mx-3 mt-3 lg:mx-6 lg:mt-4 bg-slate-900/65 backdrop-blur-2xl border border-white/10 rounded-2xl lg:rounded-3xl px-4 py-3 lg:px-6 lg:py-3.5 flex justify-between items-center gap-3 shadow-lg transition-all">

                <!-- Kiri: Tombol Toggle Sidebar + Greeting & Jam Real-Time WIB -->
                <div class="flex items-center gap-3 lg:gap-4 min-w-0">

                    <!-- Tombol Hamburger (Mobile/Tablet) -->
                    <button type="button" @click="mobileOpen = !mobileOpen"
                        class="lg:hidden p-2 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:text-teal-300 hover:border-teal-500/30 active:scale-95 transition-all cursor-pointer shrink-0"
                        title="Buka Menu">
                        <x-heroicon-o-bars-3-bottom-left class="w-5 h-5" />
                    </button>

                    <!-- Tombol Toggle Collapse (Desktop) -->
                    <button type="button" @click="toggleDesktop()"
                        class="hidden lg:inline-flex p-2 rounded-xl bg-white/5 border border-white/10 text-slate-300 hover:text-teal-300 hover:bg-teal-500/10 hover:border-teal-500/30 active:scale-95 transition-all duration-300 cursor-pointer shrink-0"
                        :title="collapsed ? 'Perbesar Sidebar' : 'Perkecil Sidebar'">
                        <x-heroicon-o-bars-3-bottom-left x-show="collapsed" x-cloak class="w-5 h-5" />
                        <x-heroicon-o-bars-3-center-left x-show="!collapsed" class="w-5 h-5" />
                    </button>

                    <h2 class="text-lg lg:text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-emerald-400 tracking-tight truncate">
                        Workspace
                    </h2>

                    <div
                        x-data="{
                            dateText: '{{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}',
                            timeText: '{{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }}',
                            timer: null,
                            updateClock() {
                                const now = new Date();
                                this.dateText = new Intl.DateTimeFormat('id-ID', {
                                    weekday: 'long',
                                    day: '2-digit',
                                    month: 'long',
                                    year: 'numeric',
                                    timeZone: 'Asia/Jakarta'
                                }).format(now);
                                this.timeText = new Intl.DateTimeFormat('id-ID', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit',
                                    hour12: false,
                                    timeZone: 'Asia/Jakarta'
                                }).format(now).replace(/\./g, ':');
                            },
                            init() {
                                this.updateClock();
                                this.timer = setInterval(() => this.updateClock(), 1000);
                            },
                            destroy() {
                                clearInterval(this.timer);
                            }
                        }"
                        class="hidden md:flex items-center gap-2.5 text-sm text-slate-400 font-medium shrink-0"
                    >
                        <span class="text-white/20">|</span>
                        <span class="hidden xl:inline" x-text="dateText"></span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-teal-500/10 border border-teal-500/20 text-teal-300 font-mono text-xs">
                            <x-heroicon-o-clock class="w-3.5 h-3.5 shrink-0" />
                            <span x-text="timeText + ' WIB'"></span>
                        </span>
                    </div>
                </div>

                <!-- Kanan: Notifikasi Real-Time & Profil User -->
                <div class="flex items-center gap-2 lg:gap-3 shrink-0">

                    <!-- Dropdown Notifikasi Stok Kritis -->
                    <div x-data="{ notifOpen: false }" class="relative">
                        <button type="button" @click="notifOpen = !notifOpen"
                            class="relative p-2 rounded-xl text-slate-400 hover:text-teal-400 hover:bg-teal-500/10 transition-all duration-200 cursor-pointer"
                            title="Notifikasi Stok Kritis">
                            <x-heroicon-o-bell class="w-5 h-5 lg:w-6 lg:h-6" />

                            @if ($lowStockCount > 0)
                                <span class="absolute top-1 right-1 flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 shadow-[0_0_8px_rgba(225,29,72,0.8)]"></span>
                                </span>
                            @endif
                        </button>

                        <div x-show="notifOpen"
                             x-cloak
                             @click.outside="notifOpen = false"
                             x-transition:enter="transition ease-out duration-200 liquid-spring"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="fixed sm:absolute right-3 sm:right-0 left-3 sm:left-auto mt-3 sm:w-80 bg-slate-900/95 backdrop-blur-2xl border border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50">

                            <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between">
                                <span class="text-sm font-bold text-white">Notifikasi Stok</span>
                                @if ($lowStockCount > 0)
                                    <span class="px-2 py-0.5 rounded-lg bg-rose-500/20 text-rose-400 text-xs font-semibold">
                                        {{ $lowStockCount }} Kritis
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-400 text-xs font-semibold">
                                        Aman
                                    </span>
                                @endif
                            </div>

                            <div class="max-h-72 overflow-y-auto divide-y divide-white/5">
                                @forelse ($lowStockItems as $low)
                                    <div class="px-4 py-3 hover:bg-white/5 transition-colors flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-white truncate">{{ $low->name }}</p>
                                            <p class="text-xs text-slate-400 font-mono">{{ $low->sku }}</p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-lg bg-rose-500/20 text-rose-400 text-xs font-bold whitespace-nowrap">
                                            Sisa {{ $low->stock }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="px-4 py-6 text-center">
                                        <x-heroicon-o-check-circle class="w-8 h-8 text-emerald-400 mx-auto mb-2" />
                                        <p class="text-sm font-medium text-white">Semua Stok Aman</p>
                                        <p class="text-xs text-slate-400 mt-0.5">Tidak ada barang dengan stok &le; 10 unit.</p>
                                    </div>
                                @endforelse
                            </div>

                            @if ($lowStockCount > 0 && $currentUser && $currentUser->hasMenuAccess('transaksi.masuk'))
                                <div class="p-2.5 border-t border-white/10 bg-white/[0.02]">
                                    <a href="{{ route('transaksi.masuk') }}" wire:navigate @click="notifOpen = false"
                                        class="block w-full text-center py-2 rounded-xl bg-teal-500/15 hover:bg-teal-500/25 text-teal-300 text-xs font-semibold transition-colors">
                                        Restock via Barang Masuk &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="h-7 w-px bg-white/10"></div>

                    <!-- Dropdown Profil Dinamis -->
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="flex items-center gap-2.5 lg:gap-3 hover:bg-white/5 p-1 lg:p-1.5 lg:pr-3 rounded-2xl transition-all border border-transparent hover:border-white/10 cursor-pointer">
                            <div class="avatar">
                                <div class="w-8 lg:w-9 rounded-xl border border-teal-500/30 shadow-[0_0_10px_rgba(20,184,166,0.2)]">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser->name ?? 'Admin') }}&background=0D9488&color=fff&bold=true&rounded=true" alt="User Avatar" />
                                </div>
                            </div>
                            <div class="text-left hidden sm:block">
                                <p class="text-sm font-bold text-white leading-tight">{{ $currentUser->name ?? 'Admin Utama' }}</p>
                                <p class="text-xs text-teal-400 font-medium">{{ $currentUser->role?->name ?? 'User' }}</p>
                            </div>
                            <x-heroicon-o-chevron-down class="w-4 h-4 text-slate-400 hidden sm:block" />
                        </div>

                        <ul tabindex="0" class="mt-4 z-[1] p-2 shadow-2xl menu menu-sm dropdown-content bg-slate-900/95 backdrop-blur-2xl border border-white/10 rounded-2xl w-56 text-slate-300">
                            <li>
                                <a href="{{ route('profil') }}" wire:navigate class="hover:bg-teal-500/20 hover:text-teal-300 rounded-xl py-2.5 transition-colors">
                                    <x-heroicon-o-user class="w-4 h-4 mr-2" /> Profil &amp; Password
                                </a>
                            </li>
                            @if ($currentUser && $currentUser->hasMenuAccess('setting.menu'))
                                <li>
                                    <a href="{{ route('setting.menu') }}" wire:navigate class="hover:bg-teal-500/20 hover:text-teal-300 rounded-xl py-2.5 transition-colors">
                                        <x-heroicon-o-cog-8-tooth class="w-4 h-4 mr-2" /> Manajemen Menu
                                    </a>
                                </li>
                            @endif
                            <div class="divider my-1 bg-white/5 h-[1px]"></div>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="p-0 w-full">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center text-rose-400 hover:bg-rose-500/20 hover:text-rose-300 rounded-xl px-3 py-2.5 transition-colors font-medium cursor-pointer">
                                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4 mr-2" /> Keluar Sistem
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>

                </div>
            </header>

            <!-- Area Konten Halaman (pb-28 di mobile/tablet agar tidak tertutup Glassy Bottom Dock) -->
            <div class="flex-1 page-fluid-enter pb-28 lg:pb-0">
                {{ $slot }}
            </div>

        </main>

        <!-- ==================== LIQUID GLASSY NAVIGATION TABS (MOBILE & TABLET) ==================== -->
        @persist('mobile-glass-dock')
            <div
                x-data="{
                    activePath: window.location.pathname,
                    pillLeft: 0,
                    pillWidth: 0,
                    pillVisible: false,
                    syncIndicator(targetPath = null) {
                        this.activePath = targetPath ?? window.location.pathname;
                        this.$nextTick(() => {
                            const items = this.$refs.dockTrack?.querySelectorAll('[data-tab-path]') ?? [];
                            let matched = null;
                            items.forEach((el) => {
                                const p = el.dataset.tabPath;
                                if (p === '/' ? this.activePath === '/' : this.activePath.startsWith(p)) {
                                    matched = el;
                                }
                            });
                            if (matched) {
                                this.pillLeft = matched.offsetLeft;
                                this.pillWidth = matched.offsetWidth;
                                this.pillVisible = true;
                            } else {
                                this.pillVisible = false;
                            }
                        });
                    },
                    init() {
                        this.syncIndicator();
                        this._onNavigated = () => this.syncIndicator();
                        document.addEventListener('livewire:navigated', this._onNavigated);
                    },
                    destroy() {
                        document.removeEventListener('livewire:navigated', this._onNavigated);
                    }
                }"
                @resize.window="syncIndicator(activePath)"
                class="lg:hidden fixed bottom-4 left-1/2 -translate-x-1/2 z-40 w-[calc(100%-1.5rem)] max-w-md"
            >
                <div class="liquid-glass-dock rounded-full p-1.5">
                    <div x-ref="dockTrack" class="relative flex items-center justify-between">

                        <!-- Sliding Liquid Glass Pill (meluncur instan saat disentuh) -->
                        <div
                            x-show="pillVisible"
                            :style="`transform: translateX(${pillLeft}px); width: ${pillWidth}px;`"
                            class="liquid-glass-pill pointer-events-none absolute top-0 bottom-0 left-0 rounded-full transition-all duration-500 liquid-spring">
                        </div>

                        @foreach ($mobileQuickTabs as $tab)
                            @php
                                $tabUrl  = route($tab['route']);
                                $tabPath = parse_url($tabUrl, PHP_URL_PATH) ?: '/';
                            @endphp
                            <a href="{{ $tabUrl }}"
                               wire:navigate
                               data-tab-path="{{ $tabPath }}"
                               @click="syncIndicator('{{ $tabPath }}')"
                               :class="(('{{ $tabPath }}' === '/' && activePath === '/') || ('{{ $tabPath }}' !== '/' && activePath.startsWith('{{ $tabPath }}')))
                                   ? 'text-white'
                                   : 'text-slate-300/80 hover:text-white'"
                               class="relative z-10 flex-1 flex flex-col items-center justify-center gap-0.5 py-2 px-2 rounded-full transition-colors duration-300 select-none">
                                <x-dynamic-component :component="$tab['icon']"
                                    class="w-5 h-5 transition-transform duration-300 liquid-spring"
                                    ::class="(('{{ $tabPath }}' === '/' && activePath === '/') || ('{{ $tabPath }}' !== '/' && activePath.startsWith('{{ $tabPath }}'))) ? 'scale-110 -translate-y-0.5 text-teal-100 drop-shadow-[0_0_8px_rgba(94,234,212,0.75)]' : ''" />
                                <span class="text-[11px] font-semibold tracking-tight leading-none">{{ $tab['label'] }}</span>
                            </a>
                        @endforeach

                        <!-- Tombol Buka Semua Menu (Sidebar Drawer) -->
                        <button type="button"
                            @click="$dispatch('toggle-mobile-sidebar')"
                            class="relative z-10 flex-1 flex flex-col items-center justify-center gap-0.5 py-2 px-2 rounded-full text-slate-300/80 hover:text-teal-300 active:scale-95 transition-all duration-200 cursor-pointer select-none">
                            <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                            <span class="text-[11px] font-semibold tracking-tight leading-none">Menu</span>
                        </button>

                    </div>
                </div>
            </div>
        @endpersist

    </div>

    @livewireScripts
</body>
</html>