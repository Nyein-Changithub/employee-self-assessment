@extends('layouts.app')
@section('title', $cycle->title)
@section('content')
<div class="bg-white rounded-xl border-t-8 border-green-600 shadow-sm p-6">
    <h1 class="text-2xl font-semibold">{{ $cycle->title }}</h1>
    @if (session('already_submitted'))
        <div class="mt-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">{{ __('You have already submitted this assessment form.') }}</div>
    @else
        <div class="mt-3 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ __('Your assessment was submitted successfully.') }}</div>
    @endif
    <p class="text-sm text-slate-500 mt-2">{{ __('Submitted at') }}: {{ $assessment->submitted_at->format('Y-m-d H:i') }}</p>
</div>

@include('partials.readonly-body', ['assessment' => $assessment, 'answers' => $answers])
@endsection
