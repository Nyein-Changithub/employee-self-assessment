@extends('layouts.admin')
@php $breadcrumbs = [[__('Home'), route('admin.dashboard')], [__('Users'), route('admin.users.index')], [$user->name, null]]; @endphp
@section('title', $user->name)
@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← {{ __('Back to list') }}</a>
        <h1 class="mt-1 text-xl font-semibold">{{ __('User Details') }}</h1>
    </div>
    <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg border border-indigo-300 px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50">{{ __('Edit') }}</a>
</div>

<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-center gap-4">
        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-xl font-semibold text-white">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
        <div class="min-w-0">
            <div class="truncate text-lg font-semibold">{{ $user->name }}</div>
            <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium uppercase text-indigo-700">{{ $user->roleName() }}</span>
        </div>
    </div>
    <dl class="grid gap-x-8 gap-y-5 text-sm sm:grid-cols-2">
        @foreach ([
            __('ID') => $user->id,
            __('Employee ID') => $user->employee_id,
            __('Email') => $user->email,
            __('Department') => $user->department,
            __('Position') => $user->position,
            __('Submissions') => $submissions,
            __('Created At') => $user->created_at?->format('Y-m-d H:i'),
            __('Last updated') => $user->updated_at?->format('Y-m-d H:i'),
        ] as $label => $value)
            <div><dt class="text-slate-500">{{ $label }}</dt><dd class="mt-1 break-words font-medium">{{ $value === null || $value === '' ? '—' : $value }}</dd></div>
        @endforeach
    </dl>
    <div class="mt-6 border-t border-slate-100 pt-5">
        <div class="mb-2 text-sm text-slate-500">{{ __('Permissions') }}</div>
        <div class="flex flex-wrap gap-2">
            @forelse ($permissions as $p)
                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 font-mono text-xs text-slate-700">{{ $p }}</span>
            @empty
                <span class="text-sm text-slate-500">{{ $user->hasRole('admin') ? __('All permissions') : __('None') }}</span>
            @endforelse
        </div>
        @if ($user->hasRole('admin'))<p class="mt-2 text-xs text-slate-500">{{ __('Administrators have every permission, including ones created later.') }}</p>@endif
    </div>
</div>
@endsection
