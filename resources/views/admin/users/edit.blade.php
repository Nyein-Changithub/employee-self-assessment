@extends('layouts.admin')
@php $breadcrumbs = [[__('Home'), route('admin.dashboard')], [__('Users'), route('admin.users.index')], [$user->name, route('admin.users.show', $user)], [__('Edit'), null]]; @endphp
@section('title', __('Edit User'))
@section('content')
<div>
    <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← {{ __('Back to list') }}</a>
    <h1 class="mt-1 text-xl font-semibold">{{ __('Edit User') }}</h1>
</div>
@include('partials.errors')
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf @method('PUT')
    @include('admin.users._form', ['user' => $user])
    <div class="flex gap-3 border-t border-slate-100 pt-4">
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Save') }}</button>
        <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
