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
            <a href="{{ auth()->check() ? route('admin.assessments.index') : '#' }}" class="font-semibold text-indigo-700">{{ __('Employee Self-Assessment') }}</a>
            <div class="flex items-center gap-4 text-sm">
                @auth
                    @if (auth()->user()->isAdminRole())
                        <a href="{{ route('admin.cycles.index') }}" class="hover:text-indigo-700">{{ __('Cycles') }}</a>
                        <a href="{{ route('admin.assessments.index') }}" class="hover:text-indigo-700">{{ __('Submissions') }}</a>
                        <form method="POST" action="{{ route('admin.logout') }}">@csrf
                            <button class="hover:text-indigo-700">{{ __('Log out') }}</button>
                        </form>
                    @endif
                @endauth
                <div class="inline-flex rounded-full border border-slate-300 overflow-hidden text-xs" role="group">
                    <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 {{ app()->getLocale() === 'en' ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-slate-50' }}">English</a>
                    <a href="{{ route('lang.switch', 'mm') }}" class="px-3 py-1 {{ app()->getLocale() === 'mm' ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-slate-50' }}">မြန်မာ</a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6 space-y-4">
        @if (session('status'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
