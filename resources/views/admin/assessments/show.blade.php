@extends('layouts.admin')
@section('title', $assessment->user->name)
@section('content')
<div class="flex flex-wrap items-center justify-between gap-2 print:hidden">
    <a href="{{ route('admin.assessments.index') }}" class="text-sm text-indigo-600">{{ __('Back') }}</a>
    <div class="flex gap-3 text-sm">
        <button onclick="window.print()" class="text-indigo-600 hover:underline">{{ __('Print') }}</button>
        <a href="{{ route('admin.assessments.pdf', $assessment) }}" class="text-indigo-600 hover:underline">{{ __('Download PDF') }}</a>
    </div>
</div>
<div class="bg-white rounded-xl shadow-sm p-6">
    <h1 class="text-xl font-semibold">{{ $assessment->cycle->title }}</h1>
    <p class="text-sm text-slate-500">{{ __('Submitted at') }}: {{ $assessment->submitted_at?->format('Y-m-d H:i') }}</p>
</div>
@include('partials.readonly-body', ['assessment' => $assessment, 'answers' => $answers])
@endsection
