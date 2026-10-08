@extends('layouts.app')
@section('title', $period->title)
@section('content')
@php
    $input = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none';
@endphp
<div class="bg-white rounded-xl border-t-8 border-indigo-600 shadow-sm p-6">
    <h1 class="text-2xl font-semibold">{{ $period->title }}</h1>
    <p class="text-sm text-slate-500 mt-1">{{ __('Once submitted, your answers cannot be changed.') }}</p>
</div>

<form method="POST" action="{{ route('assessment.submit', $period->slug) }}" class="space-y-4">
    @csrf
    <section class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <h2 class="font-semibold">{{ __('Your Details') }}</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium mb-1">{{ __('Full Name') }} <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required placeholder="{{ __('e.g. Aung Aung') }}" class="{{ $input }}">
                @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="employee_id" class="block text-sm font-medium mb-1">{{ __('Employee ID') }} <span class="text-red-500">*</span></label>
                <input id="employee_id" name="employee_id" type="text" value="{{ old('employee_id') }}" required placeholder="{{ __('e.g. EMP-0001') }}" class="{{ $input }}">
                @error('employee_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium mb-1">{{ __('Email') }} <span class="text-slate-400 font-normal">({{ __('optional') }})</span></label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@company.com" class="{{ $input }}">
                @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="position" class="block text-sm font-medium mb-1">{{ __('Position') }} <span class="text-red-500">*</span></label>
                <select id="position" name="position" required class="{{ $input }}">
                    <option value="" disabled @selected(! old('position'))>{{ __('Select position') }}</option>
                    @foreach ($positions as $p)
                        <option value="{{ $p->name_en }}" @selected(old('position') === $p->name_en)>{{ $p->label() }}</option>
                    @endforeach
                </select>
                @error('position')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="department" class="block text-sm font-medium mb-1">{{ __('Department') }} <span class="text-red-500">*</span></label>
                <select id="department" name="department" required class="{{ $input }}">
                    <option value="" disabled @selected(! old('department'))>{{ __('Select department') }}</option>
                    @foreach ($departments as $d)
                        <option value="{{ $d->name_en }}" @selected(old('department') === $d->name_en)>{{ $d->label() }}</option>
                    @endforeach
                </select>
                @error('department')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    @foreach ($questions as $question)
        <details open class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            {{-- Question --}}
            <summary class="flex cursor-pointer list-none items-start gap-3 bg-indigo-50 px-5 py-4 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-indigo-600 [&::-webkit-details-marker]:hidden">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">Q{{ $loop->iteration }}</span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-900 break-words">{{ $question->text() }} <span class="text-red-500">*</span></p>
                    @if ($question->guide())
                        <p class="mt-1 text-sm text-slate-600">{{ $question->guide() }}</p>
                    @endif
                </div>
                <svg class="mt-1.5 h-5 w-5 shrink-0 text-indigo-600 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/>
                </svg>
            </summary>
            {{-- Answer --}}
            <div class="border-t border-indigo-100 p-5">
                <label for="answer-{{ $question->id }}" class="mb-2 flex items-center gap-2 text-sm font-medium text-emerald-700">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-xs font-semibold text-white">A</span>
                    {{ __('Your answer') }}
                </label>
                <textarea id="answer-{{ $question->id }}" name="answers[{{ $question->id }}]" rows="4" required
                          oninvalid="this.closest('details').open = true"
                          placeholder="{{ __('Type your answer here...') }}"
                          class="w-full rounded-lg border-2 border-emerald-200 bg-emerald-50/40 px-3 py-2 focus:border-emerald-500 focus:bg-white focus:outline-none">{{ old("answers.{$question->id}") }}</textarea>
                @error("answers.{$question->id}")<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </details>
    @endforeach

    <button class="w-full sm:w-auto rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3">
        {{ __('Submit Assessment') }}
    </button>
</form>
@endsection
