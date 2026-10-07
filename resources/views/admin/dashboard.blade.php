@extends('layouts.admin')
@php
    $breadcrumbs = [[__('Home'), null]];
@endphp
@section('title', __('Dashboard'))
@section('content')
<h1 class="text-xl font-semibold">{{ __('Dashboard') }}</h1>

@php
    $themes = [
        'periods' => ['border' => 'border-l-indigo-500', 'from' => 'from-indigo-50', 'icon' => 'bg-indigo-100 text-indigo-600', 'path' => 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
        'submissions' => ['border' => 'border-l-emerald-500', 'from' => 'from-emerald-50', 'icon' => 'bg-emerald-100 text-emerald-600', 'path' => 'M7 3h8l4 4v14H7zM15 3v4h4M10 13h6M10 17h6'],
        'employees' => ['border' => 'border-l-sky-500', 'from' => 'from-sky-50', 'icon' => 'bg-sky-100 text-sky-600', 'path' => 'M17 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM21 20v-1a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8'],
        'departments' => ['border' => 'border-l-amber-500', 'from' => 'from-amber-50', 'icon' => 'bg-amber-100 text-amber-600', 'path' => 'M4 21V5l8-2v18M12 9h8v12M7 9h2M7 13h2M7 17h2M15 13h2M15 17h2M3 21h18'],
        'positions' => ['border' => 'border-l-violet-500', 'from' => 'from-violet-50', 'icon' => 'bg-violet-100 text-violet-600', 'path' => 'M3 8h18v12H3zM8 8V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v3M3 13h18'],
    ];
@endphp
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
    @foreach ($stats as $stat)
        @php $t = $themes[$stat['key']]; @endphp
        <a href="{{ route($stat['route']) }}"
           class="group flex items-start justify-between gap-3 rounded-xl border border-l-4 border-slate-200 {{ $t['border'] }} bg-gradient-to-br {{ $t['from'] }} to-white p-5 shadow-sm transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-xl motion-reduce:transition-none motion-reduce:hover:translate-y-0">
            <div class="min-w-0">
                <div class="break-words text-sm leading-tight text-slate-500">{{ __($stat['label']) }}</div>
                <div class="mt-1 text-3xl font-semibold text-slate-900">{{ number_format($stat['value']) }}</div>
            </div>
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $t['icon'] }} transition-transform duration-300 group-hover:scale-110 motion-reduce:transition-none">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $t['path'] }}"/></svg>
            </span>
        </a>
    @endforeach
</div>

<div class="grid gap-4 lg:grid-cols-3">
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all duration-300 ease-in-out hover:shadow-lg lg:col-span-2">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
            <h2 class="font-semibold">{{ __('Recent Submissions') }}</h2>
            <a href="{{ route('admin.assessments.index') }}" class="text-sm text-indigo-600 hover:underline">{{ __('View all') }}</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                @forelse ($recent as $a)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3"><div class="font-medium">{{ $a->user->name }}</div><div class="text-xs text-slate-500">{{ $a->user->department }}</div></td>
                        <td class="px-5 py-3 text-slate-600">{{ $a->period->title }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-slate-500">{{ $a->submitted_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-5 py-3 text-right"><a href="{{ route('admin.assessments.show', $a) }}" class="rounded-lg border border-slate-300 px-3 py-1 hover:bg-slate-100">{{ __('View') }}</a></td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-10 text-center text-slate-500">{{ __('No submissions found.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all duration-300 ease-in-out hover:shadow-lg">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
            <h2 class="font-semibold">{{ __('Assessment Periods') }}</h2>
            <a href="{{ route('admin.periods.index') }}" class="text-sm text-indigo-600 hover:underline">{{ __('View all') }}</a>
        </div>
        <ul class="divide-y divide-slate-100 text-sm">
            @foreach ($periods as $c)
                <li class="flex items-center justify-between gap-3 px-5 py-3">
                    <span class="truncate font-medium">{{ $c->title }}</span>
                    <span class="shrink-0 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">{{ $c->assessments_count }}</span>
                </li>
            @endforeach
        </ul>
    </section>
</div>
@endsection
