<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Login — Inventura.' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#020a0b] text-slate-200 antialiased selection:bg-teal-500/30 selection:text-teal-200 overflow-x-hidden">
    {{ $slot }}
    @livewireScripts
</body>
</html>