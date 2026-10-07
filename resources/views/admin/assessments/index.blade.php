@extends('layouts.admin')
@php
    $breadcrumbs = [[__('Home'), route('admin.dashboard')], [__('Submissions'), null]];
@endphp
@section('content')
<h1 class="text-xl font-semibold">{{ __('Submissions') }}</h1>

<form method="GET" class="bg-white rounded-xl shadow-sm p-4 flex flex-wrap gap-3 items-end">
    <select name="period" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <option value="">{{ __('All periods') }}</option>
        @foreach ($periods as $c)<option value="{{ $c->id }}" @selected(($filters['period'] ?? null) == $c->id)>{{ $c->title }}</option>@endforeach
    </select>
    <select name="department" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <option value="">{{ __('All departments') }}</option>
        @foreach ($departments as $d)<option @selected(($filters['department'] ?? null) === $d)>{{ $d }}</option>@endforeach
    </select>
    <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm">{{ __('Filter') }}</button>
    <div class="flex gap-3 text-sm sm:ml-auto">
        <a href="{{ route('admin.assessments.export.excel', $filters) }}" class="text-indigo-600 hover:underline">{{ __('Export Excel') }}</a>
        <a href="{{ route('admin.assessments.export.csv', $filters) }}" class="text-indigo-600 hover:underline">{{ __('Export CSV') }}</a>
    </div>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500 border-b">
            <tr><th class="p-3">{{ __('Employee ID') }}</th><th class="p-3">{{ __('Name') }}</th><th class="p-3">{{ __('Department') }}</th><th class="p-3">{{ __('Assessment Period') }}</th><th class="p-3">{{ __('Submitted at') }}</th><th class="p-3"></th></tr>
        </thead>
        <tbody class="divide-y">
        @forelse ($assessments as $a)
            <tr>
                <td class="p-3">{{ $a->user->employee_id }}</td>
                <td class="p-3">{{ $a->user->name }}</td>
                <td class="p-3">{{ $a->user->department }}</td>
                <td class="p-3">{{ $a->period->title }}</td>
                <td class="p-3 whitespace-nowrap">{{ $a->submitted_at?->format('Y-m-d H:i') }}</td>
                <td class="p-3 whitespace-nowrap space-x-3">
                    <a href="{{ route('admin.assessments.show', $a) }}" class="text-indigo-600 hover:underline">{{ __('View') }}</a>
                    <a href="{{ route('admin.assessments.pdf', $a) }}" class="text-indigo-600 hover:underline">{{ __('PDF') }}</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="p-6 text-center text-slate-500">{{ __('No submissions found.') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $assessments->links() }}
@endsection
