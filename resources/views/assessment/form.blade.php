@extends('layouts.app')
@section('title', $cycle->title)
@section('content')
<div class="bg-white rounded-xl border-t-8 border-indigo-600 shadow-sm p-6">
    <h1 class="text-2xl font-semibold">{{ $cycle->title }}</h1>
    <p class="text-sm text-slate-500 mt-1">{{ __('Once submitted, your answers cannot be changed.') }}</p>
</div>

<form method="POST" action="{{ route('assessment.submit', $cycle->slug) }}" class="space-y-4">
    @csrf
    <section class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <h2 class="font-semibold">{{ __('Your Details') }}</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ([
                'name' => ['Full Name', 'text'],
                'employee_id' => ['Employee ID', 'text'],
                'email' => ['Email', 'email'],
                'position' => ['Position', 'text'],
                'department' => ['Department', 'text'],
            ] as $field => [$label, $type])
                <div>
                    <label for="{{ $field }}" class="block text-sm font-medium mb-1">{{ __($label) }} <span class="text-red-500">*</span></label>
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" required
                           class="w-full rounded-lg border-slate-300 border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error($field)<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
    </section>

    @foreach ($questions as $question)
        <section class="bg-white rounded-xl shadow-sm p-6">
            <label for="answer-{{ $question->id }}" class="block font-medium">
                {{ $loop->iteration }}. {{ $question->text() }} <span class="text-red-500">*</span>
            </label>
            @if ($question->guide())
                <p class="text-sm text-slate-500 mt-1">{{ $question->guide() }}</p>
            @endif
            <textarea id="answer-{{ $question->id }}" name="answers[{{ $question->id }}]" rows="4" required
                      placeholder="{{ __('Your answer') }}"
                      class="mt-3 w-full rounded-lg border-slate-300 border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old("answers.{$question->id}") }}</textarea>
            @error("answers.{$question->id}")<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </section>
    @endforeach

    <button class="w-full sm:w-auto rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3">
        {{ __('Submit Assessment') }}
    </button>
</form>
@endsection
