<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Inventura</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-teal-950 to-slate-950 text-slate-200 antialiased selection:bg-teal-500/30 selection:text-teal-200 flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Efek Cahaya Latar (Ambient Glow) -->
    <div class="pointer-events-none fixed -top-32 -left-32 w-96 h-96 rounded-full bg-teal-500/15 blur-3xl"></div>
    <div class="pointer-events-none fixed -bottom-32 -right-32 w-96 h-96 rounded-full bg-emerald-500/15 blur-3xl"></div>

    <div class="w-full max-w-md relative z-10 page-fluid-enter">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>