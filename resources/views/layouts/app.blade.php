<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'mm' ? 'my' : 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Employee Self-Assessment'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="bg-white border-b border-slate-200 print:hidden">
        <div class="max-w-4xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <span class="font-semibold text-indigo-700">{{ __('Employee Self-Assessment') }}</span>
            @include('partials.lang-switch')
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6 space-y-4">
        @include('partials.flash')
        @yield('content')
    </main>
    @include('partials.ui')
</body>
</html>
