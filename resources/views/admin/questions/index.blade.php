@extends('layouts.app')
@section('title', $cycle->title)
@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">{{ $cycle->title }} — {{ __('Questions') }}</h1>
    <a href="{{ route('admin.cycles.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 px-4 py-2 text-sm">← {{ __('Back') }}</a>
</div>

@if ($errors->any())
    <div data-autodismiss role="alert" class="flex items-start justify-between gap-3 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm transition-opacity duration-500">
        <ul class="list-disc ml-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" data-close aria-label="Close" class="shrink-0 rounded p-1 opacity-60 hover:opacity-100 hover:bg-black/5">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M4.3 4.3a1 1 0 0 1 1.4 0L10 8.6l4.3-4.3a1 1 0 1 1 1.4 1.4L11.4 10l4.3 4.3a1 1 0 0 1-1.4 1.4L10 11.4l-4.3 4.3a1 1 0 0 1-1.4-1.4L8.6 10 4.3 5.7a1 1 0 0 1 0-1.4z"/></svg>
            </button>
    </div>
@endif

@forelse ($questions as $question)
    <div class="relative" data-row>
        <details class="bg-white rounded-xl shadow-sm">
            <summary class="p-4 pr-52 cursor-pointer">
                <span><span class="text-slate-400 mr-2">Q: {{ $question->order_no }}</span>{{ $question->question_en }}
                    @unless ($question->is_active)<span class="ml-2 text-xs rounded bg-slate-200 px-2 py-0.5">inactive</span>@endunless
                </span>
            </summary>
            <div class="p-4 border-t">
                <form method="POST" action="{{ route('admin.questions.update', $question) }}" class="space-y-3">
                    @csrf @method('PUT')
                    @include('admin.questions._fields', ['q' => $question, 'nextOrder' => null])
                    <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm">{{ __('Save') }}</button>
                </form>
            </div>
        </details>
        <div class="absolute top-3 right-4 flex items-center gap-3 text-sm">
            <button type="button" data-toggle-details class="rounded-lg border border-indigo-300 text-indigo-700 hover:bg-indigo-50 px-3 py-1">{{ __('Edit') }}</button>
            <form method="POST" action="{{ route('admin.questions.destroy', $question) }}"
                  data-confirm
                  data-confirm-title="{{ __('Delete question?') }}"
                  data-confirm-item="{{ $question->question_en }}"
                  data-confirm-message="{{ __('This cannot be undone. Any submitted answers to this question will also be permanently deleted.') }}"
                  data-confirm-ok="{{ __('Yes, delete') }}">
                @csrf @method('DELETE')
                <button class="rounded-lg border border-red-300 text-red-600 hover:bg-red-50 px-3 py-1">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>
@empty
    <p class="text-slate-500">{{ __('No questions yet.') }}</p>
@endforelse

<form method="POST" action="{{ route('admin.questions.store', $cycle) }}" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
    @csrf
    <h2 class="font-semibold">{{ __('Add Question') }}</h2>
    @include('admin.questions._fields', ['q' => null])
    <div class="flex items-center gap-3">
        <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm">{{ __('Add Question') }}</button>
        <a href="{{ route('admin.cycles.index') }}" class="rounded-lg border border-slate-300 hover:bg-slate-50 px-4 py-2 text-sm">{{ __('Back') }}</a>
    </div>
</form>
@endsection
