@extends('layouts.admin')
@section('title', __('Dashboard'))
@section('content')
<h1 class="text-xl font-semibold">{{ __('Dashboard') }}</h1>

<div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
    @foreach ($stats as $stat)
        <a href="{{ route($stat['route']) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow">
            <div class="text-sm text-slate-500">{{ __($stat['label']) }}</div>
            <div class="mt-1 text-3xl font-semibold text-slate-900">{{ number_format($stat['value']) }}</div>
        </a>
    @endforeach
</div>

<div class="grid gap-4 lg:grid-cols-3">
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
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

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
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
