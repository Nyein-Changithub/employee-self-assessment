@php
    $icons = [
        'home' => 'M3 12l9-9 9 9M5 10v10h14V10',
        'clipboard' => 'M9 5h6M9 9h6M9 13h4M7 3h10a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z',
        'document' => 'M7 3h8l4 4v14H7zM15 3v4h4M10 13h6M10 17h6',
        'building' => 'M4 21V5l8-2v18M12 9h8v12M7 9h2M7 13h2M7 17h2M15 13h2M15 17h2M3 21h18',
        'briefcase' => 'M3 8h18v12H3zM8 8V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v3M3 13h18',
    ];
    $nav = [
        __('Overview') => [
            [__('Dashboard'), 'admin.dashboard', ['admin.dashboard'], 'home'],
        ],
        __('Assessments') => [
            [__('Cycles'), 'admin.cycles.index', ['admin.cycles.*', 'admin.questions.*'], 'clipboard'],
            [__('Submissions'), 'admin.assessments.index', ['admin.assessments.*'], 'document'],
        ],
        __('Master Data') => [
            [__('Departments'), 'admin.departments.index', ['admin.departments.*'], 'building'],
            [__('Positions'), 'admin.positions.index', ['admin.positions.*'], 'briefcase'],
        ],
    ];
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'mm' ? 'my' : 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Employee Self-Assessment'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden print:hidden"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 text-slate-300 transition-transform duration-200 lg:translate-x-0 print:hidden">
        <div class="flex h-14 shrink-0 items-center gap-2 border-b border-slate-800 px-5">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-sm font-bold text-white">E</span>
            <span class="font-semibold text-white">{{ __('Employee Self-Assessment') }}</span>
        </div>

        <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4">
            @foreach ($nav as $section => $items)
                <div>
                    <div class="mb-1 px-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $section }}</div>
                    @foreach ($items as [$label, $route, $patterns, $icon])
                        @php $active = request()->routeIs(...$patterns); @endphp
                        <a href="{{ route($route) }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors {{ $active ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icons[$icon] }}"/></svg>
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="border-t border-slate-800 p-3">
            <div class="flex items-center gap-3 px-2 py-2">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <div class="truncate text-sm font-medium text-white">{{ $user->name }}</div>
                    <div class="text-xs uppercase text-slate-400">{{ $user->role }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf
                <button class="mt-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-800 hover:text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4M16 17l5-5-5-5M21 12H9"/></svg>
                    {{ __('Log out') }}
                </button>
            </form>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-64 print:pl-0">
        <header class="sticky top-0 z-20 flex h-14 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6 print:hidden">
            <button type="button" data-sidebar-toggle aria-label="Menu" class="rounded-lg p-2 hover:bg-slate-100 lg:hidden">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <span class="hidden text-sm text-slate-500 lg:block">{{ __('Welcome back') }}, <span class="font-medium text-slate-700">{{ $user->name }}</span></span>
            @include('partials.lang-switch')
        </header>

        <main class="mx-auto w-full max-w-6xl space-y-4 p-4 sm:p-6">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>

    @include('partials.ui')
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const setSidebar = (open) => {
            sidebar.classList.toggle('-translate-x-full', !open);
            overlay.classList.toggle('hidden', !open);
        };
        document.querySelectorAll('[data-sidebar-toggle]').forEach((b) =>
            b.addEventListener('click', () => setSidebar(sidebar.classList.contains('-translate-x-full'))));
        overlay.addEventListener('click', () => setSidebar(false));
    </script>
</body>
</html>
