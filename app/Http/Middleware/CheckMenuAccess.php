<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Jika akun dinonaktifkan oleh admin saat user masih login, langsung keluarkan
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'email' => 'Akun kamu sedang dinonaktifkan oleh Administrator.',
            ]);
        }

        // Super Administrator bebas akses semua halaman
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $currentRoute = $request->route()?->getName();

        // Route yang selalu boleh diakses semua user yang login
        $alwaysAllowed = ['profil', 'logout'];
        if (!$currentRoute || in_array($currentRoute, $alwaysAllowed)) {
            return $next($request);
        }

        // Route turunan (Export PDF/Excel mengikuti izin menu Laporan)
        $routeAliases = [
            'laporan.pdf'   => 'laporan.index',
            'laporan.excel' => 'laporan.index',
        ];
        $checkRoute = $routeAliases[$currentRoute] ?? $currentRoute;

        // Cek apakah route tersebut terdaftar di manajemen menu
        $isManagedRoute = Menu::where('route_name', $checkRoute)->exists();

        if ($isManagedRoute && !$user->hasMenuAccess($checkRoute)) {
            // Jika user baru login dan tidak punya akses ke Dashboard, arahkan ke menu pertamanya
            if ($currentRoute === 'dashboard') {
                $fallback = $user->getFirstAccessibleRoute();
                if ($fallback !== 'dashboard') {
                    return redirect()->route($fallback);
                }
            }

            abort(403, 'Akses ditolak. Role kamu tidak memiliki izin untuk membuka halaman ini.');
        }

        return $next($request);
    }
}