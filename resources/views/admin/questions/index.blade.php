@extends('layouts.app')
@section('title', $cycle->title)
@section('content')
<div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">{{ $cycle->title }} — {{ __('Questions') }}</h1>
    <a href="{{ route('admin.cycles.index') }}" class="text-sm text-indigo-600">{{ __('Back') }}</a>
</div>

@if ($errors->any())
    <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc ml-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

@forelse ($questions as $question)
    <details class="bg-white rounded-xl shadow-sm">
        <summary class="p-4 cursor-pointer flex items-start justify-between gap-3">
            <span><span class="text-slate-400 mr-2">#{{ $question->order_no }}</span>{{ $question->question_en }}
                @unless ($question->is_active)<span class="ml-2 text-xs rounded bg-slate-200 px-2 py-0.5">inactive</span>@endunless
            </span>
            <span class="text-sm text-indigo-600 shrink-0">{{ __('Edit') }}</span>
        </summary>
        <div class="p-4 border-t space-y-3">
            <form method="POST" action="{{ route('admin.questions.update', $question) }}" class="space-y-3">
                @csrf @method('PUT')
                @include('admin.questions._fields', ['q' => $question])
                <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm">{{ __('Save') }}</button>
            </form>
            <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('{{ __('Delete this question?') }}')">
                @csrf @method('DELETE')
                <button class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
            </form>
        </div>
    </details>
@empty
    <p class="text-slate-500">{{ __('No questions yet.') }}</p>
@endforelse

<form method="POST" action="{{ route('admin.questions.store', $cycle) }}" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
    @csrf
    <h2 class="font-semibold">{{ __('Add Question') }}</h2>
    @include('admin.questions._fields', ['q' => null])
    <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm">{{ __('Add Question') }}</button>
</form>
@endsection
