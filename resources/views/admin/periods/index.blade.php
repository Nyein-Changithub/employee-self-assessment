@extends('layouts.admin')
@php
    $breadcrumbs = [[__('Home'), route('admin.dashboard')], [__('Assessment Periods'), null]];
@endphp
@section('content')
<h1 class="text-xl font-semibold">{{ __('Assessment Periods') }}</h1>

<div class="space-y-4">
    @foreach ($periods as $period)
        @php $link = url('/assessment/'.$period->slug); @endphp
        <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="font-semibold text-lg">{{ $period->title }}</h2>
                    <p class="text-xs text-slate-500 mt-1">{{ __('Questions') }}: {{ $period->questions_count }} · {{ __('Submissions') }}: {{ $period->submitted_assessments_count }}</p>
                </div>
                <div class="flex gap-2 text-sm">
                    <a href="{{ route('admin.questions.index', $period) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 hover:bg-slate-50">{{ __('Manage Questions') }}</a>
                    <a href="{{ route('admin.assessments.index', ['period' => $period->id]) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 hover:bg-slate-50">{{ __('Submissions') }}</a>
                </div>
            </div>

            <div>
                <div class="text-xs font-medium text-slate-500 mb-1">{{ __('Employee link') }} <span class="font-normal">— {{ __('share this link with employees') }}</span></div>
                <div class="flex items-stretch gap-2">
                    <input type="text" readonly value="{{ $link }}" onfocus="this.select()"
                           class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                    <button type="button" data-copy="{{ $link }}" data-copied="{{ __('Copied!') }}"
                            class="shrink-0 rounded-lg border border-indigo-600 text-indigo-700 hover:bg-indigo-50 px-3 py-2 text-sm font-medium transition-colors">
                        <span data-label>{{ __('Copy') }}</span>
                    </button>
                    <a href="{{ $link }}" target="_blank" rel="noopener"
                       class="shrink-0 rounded-lg border border-slate-300 hover:bg-slate-50 px-3 py-2 text-sm">{{ __('Open') }} ↗</a>
                </div>
            </div>
        </div>
    @endforeach
</div>

<form method="POST" action="{{ route('admin.periods.store') }}" class="bg-white rounded-xl shadow-sm p-4 grid sm:grid-cols-3 gap-3 items-end">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">{{ __('Title') }}</label>
        <input name="title" value="{{ old('title') }}" required placeholder="2026 Q2 Assessment" class="w-full rounded-lg border border-slate-300 px-3 py-2">
        @error('title')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">{{ __('Slug') }}</label>
        <input name="slug" value="{{ old('slug') }}" required placeholder="2026-q2" class="w-full rounded-lg border border-slate-300 px-3 py-2">
        @error('slug')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2">{{ __('Create Period') }}</button>
</form>
@endsection
