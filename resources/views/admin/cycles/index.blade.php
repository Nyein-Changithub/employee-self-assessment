@extends('layouts.app')
@section('content')
<h1 class="text-xl font-semibold">{{ __('Cycles') }}</h1>

<div class="bg-white rounded-xl shadow-sm divide-y">
    @foreach ($cycles as $cycle)
        <div class="p-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <div class="font-medium">{{ $cycle->title }}</div>
                <a href="{{ route('assessment.show', $cycle->slug) }}" class="text-xs text-indigo-600 break-all">{{ __('Employee link') }}: {{ url('/assessment/'.$cycle->slug) }}</a>
                <div class="text-xs text-slate-500">{{ __('Questions') }}: {{ $cycle->questions_count }} · {{ __('Submissions') }}: {{ $cycle->assessments_count }}</div>
            </div>
            <div class="flex gap-3 text-sm">
                <a href="{{ route('admin.questions.index', $cycle) }}" class="text-indigo-600 hover:underline">{{ __('Manage Questions') }}</a>
                <a href="{{ route('admin.assessments.index', ['cycle' => $cycle->id]) }}" class="text-indigo-600 hover:underline">{{ __('Submissions') }}</a>
            </div>
        </div>
    @endforeach
</div>

<form method="POST" action="{{ route('admin.cycles.store') }}" class="bg-white rounded-xl shadow-sm p-4 grid sm:grid-cols-3 gap-3 items-end">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">{{ __('Title') }}</label>
        <input name="title" value="{{ old('title') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
        @error('title')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">{{ __('Slug') }}</label>
        <input name="slug" value="{{ old('slug') }}" required placeholder="2026-q2" class="w-full rounded-lg border border-slate-300 px-3 py-2">
        @error('slug')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2">{{ __('Create Cycle') }}</button>
</form>
@endsection
