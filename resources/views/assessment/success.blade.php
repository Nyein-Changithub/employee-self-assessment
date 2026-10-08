@extends('layouts.app')
@section('title', __('Submission complete'))
@section('content')
<div class="flex min-h-[65vh] items-center justify-center py-8">
    <section class="w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="h-2 bg-emerald-500"></div>
        <div class="px-6 py-10 text-center sm:px-10">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-9 w-9" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.051l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.479-9.816a.75.75 0 0 1 1.051-.143Z" clip-rule="evenodd"/>
                </svg>
            </span>

            <h1 class="mt-5 text-2xl font-semibold text-slate-900">{{ __('Submission complete') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ $period->title }}</p>

            <div role="alert" class="mt-6 rounded-xl border px-4 py-3 text-sm font-medium {{ session('already_submitted') ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">
                {{ session('already_submitted') ? __('You have already submitted this assessment form.') : __('Your assessment was submitted successfully.') }}
            </div>

            <p class="mt-5 text-sm text-slate-500">{{ __('You may now close this tab.') }}</p>
        </div>
    </section>
</div>
@endsection
