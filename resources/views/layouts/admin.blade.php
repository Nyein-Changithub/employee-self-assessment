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
            [__('Assessment Periods'), 'admin.periods.index', ['admin.periods.*', 'admin.questions.*'], 'clipboard'],
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
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased"
      x-data="{ sidebar: false, logoutOpen: false, profileOpen: false }"
      @keydown.escape.window="sidebar = false; logoutOpen = false; profileOpen = false">
    <div x-show="sidebar" x-cloak x-transition.opacity @click="sidebar = false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden print:hidden"></div>

    <aside id="sidebar" :class="{ '-translate-x-full': !sidebar }" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 text-slate-300 transition-transform duration-200 lg:translate-x-0 print:hidden">
        <a href="{{ route('admin.dashboard') }}" class="flex min-h-16 shrink-0 items-center gap-3 border-b border-slate-800 px-5 py-4">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-500 text-base font-bold text-white shadow-sm">E</span>
            <span class="min-w-0 text-sm font-semibold text-white {{ app()->getLocale() === 'mm' ? 'leading-relaxed' : 'leading-tight' }}">{{ __('Employee Self-Assessment') }}</span>
        </a>

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
            <button type="button" @click="profileOpen = true" aria-haspopup="dialog" title="{{ __('View profile') }}"
                    class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left transition-colors hover:bg-slate-800">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-700 text-sm font-semibold text-white">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-white">{{ $user->name }}</span>
                    <span class="block text-xs uppercase text-slate-400">{{ $user->roleName() }}</span>
                </span>
                <svg class="h-4 w-4 shrink-0 text-slate-500" viewBox="0 0 20 20" fill="currentColor"><path d="M7.3 4.3a1 1 0 0 1 1.4 0l5 5a1 1 0 0 1 0 1.4l-5 5a1 1 0 0 1-1.4-1.4L11.6 10 7.3 5.7a1 1 0 0 1 0-1.4z"/></svg>
            </button>
            </div>
            <button type="button" @click="logoutOpen = true" class="mt-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-slate-800 hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4M16 17l5-5-5-5M21 12H9"/></svg>
                {{ __('Log out') }}
            </button>
            <form id="logout-form" method="POST" action="{{ route('admin.logout') }}" class="hidden">@csrf</form>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-64 print:pl-0">
        <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6 print:hidden">
            <button type="button" @click="sidebar = !sidebar" :aria-expanded="sidebar" aria-label="Menu" class="rounded-lg p-2 hover:bg-slate-100 lg:hidden">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <span class="hidden text-sm text-slate-500 lg:block">{{ __('Welcome back') }}, <span class="font-medium text-slate-700">{{ $user->name }}</span></span>
            @include('partials.lang-switch')
        </header>

        <main class="mx-auto w-full max-w-6xl space-y-4 p-4 sm:p-6">
            <x-breadcrumbs :items="$breadcrumbs ?? []" />
            @include('partials.flash')
            @yield('content')
        </main>
    </div>

    @include('partials.ui')
    {{-- Profile --}}
    <div x-show="profileOpen" x-cloak x-effect="if (profileOpen) $nextTick(() => $refs.profileClose.focus())"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 print:hidden"
         role="dialog" aria-modal="true" aria-labelledby="profile-title">
        <div x-show="profileOpen" x-transition.opacity @click="profileOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div x-show="profileOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="h-24 bg-gradient-to-r from-indigo-600 to-violet-500"></div>
            <button type="button" @click="profileOpen = false" aria-label="{{ __('Close') }}" class="absolute right-3 top-3 rounded-full p-1.5 text-white/80 hover:bg-white/20 hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M4.3 4.3a1 1 0 0 1 1.4 0L10 8.6l4.3-4.3a1 1 0 1 1 1.4 1.4L11.4 10l4.3 4.3a1 1 0 0 1-1.4 1.4L10 11.4l-4.3 4.3a1 1 0 0 1-1.4-1.4L8.6 10 4.3 5.7a1 1 0 0 1 0-1.4z"/></svg>
            </button>
            <div class="px-6 pb-6">
                <div class="-mt-10 flex items-start gap-4">
                    <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full border-4 border-white bg-indigo-600 text-3xl font-semibold text-white shadow">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    <div class="min-w-0 pt-11">
                        <h2 id="profile-title" class="truncate text-lg font-semibold text-slate-900">{{ $user->name }}</h2>
                        <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium uppercase text-indigo-700">{{ $user->roleName() }}</span>
                    </div>
                </div>

                <dl class="mt-6 divide-y divide-slate-100 text-sm">
                    @foreach ([
                        __('Full Name') => $user->name,
                        __('Email Address') => $user->email,
                        __('Role') => $user->roleName(),
                        __('Department') => $user->department,
                        __('Position') => $user->position,
                        __('Account Created') => $user->created_at?->format('Y-m-d'),
                    ] as $label => $value)
                        <div class="flex items-start justify-between gap-4 py-2.5">
                            <dt class="shrink-0 text-slate-500">{{ $label }}</dt>
                            <dd class="min-w-0 break-words text-right font-medium text-slate-800">{{ $value ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-6 flex justify-end">
                    <button type="button" x-ref="profileClose" @click="profileOpen = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Logout confirmation --}}
    <div x-show="logoutOpen" x-cloak x-effect="if (logoutOpen) $nextTick(() => $refs.logoutCancel.focus())"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 print:hidden"
         role="dialog" aria-modal="true" aria-labelledby="logout-title">
        <div x-show="logoutOpen" x-transition.opacity @click="logoutOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div x-show="logoutOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4M16 17l5-5-5-5M21 12H9"/></svg>
                </div>
                <div>
                    <h2 id="logout-title" class="text-lg font-semibold text-slate-900">{{ __('Confirm Logout') }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ __('Are you sure you want to log out of your account?') }}</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-ref="logoutCancel" @click="logoutOpen = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">{{ __('Cancel') }}</button>
                <button type="submit" form="logout-form" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">{{ __('Yes, Logout') }}</button>
            </div>
        </div>
    </div>
</body>
</html>
