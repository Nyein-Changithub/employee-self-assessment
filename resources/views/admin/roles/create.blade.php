@extends('layouts.admin')
@php $breadcrumbs = [[__('Home'), route('admin.dashboard')], [__('Roles'), route('admin.roles.index')], [__('Create'), null]]; @endphp
@section('title', __('Create Role'))
@section('content')
<div>
    <a href="{{ route('admin.roles.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← {{ __('Back to list') }}</a>
    <h1 class="mt-1 text-xl font-semibold">{{ __('Create Role') }}</h1>
</div>
@include('partials.errors')
<form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @include('admin.roles._form', ['role' => null])
    <div class="flex gap-3 border-t border-slate-100 pt-4">
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Save') }}</button>
        <a href="{{ route('admin.roles.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
