@extends('layouts.admin')
@php
    $breadcrumbs = [[__('Home'), route('admin.dashboard')], [__($labels['plural']), route("admin.{$type}.index")], [$item->name_en, route("admin.{$type}.show", $item->id)], [__('Edit'), null]];
@endphp
@section('title', __($labels['edit']))
@section('content')
<div>
    <a href="{{ route("admin.{$type}.index") }}" class="text-sm text-slate-500 hover:text-slate-800">← {{ __('Back to list') }}</a>
    <h1 class="mt-1 text-xl font-semibold">{{ __($labels['edit']) }} <span class="text-slate-400">#{{ $item->id }}</span></h1>
</div>

<form method="POST" action="{{ route("admin.{$type}.update", $item->id) }}" class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf @method('PUT')
    @include('admin.lookups._form', ['item' => $item])
    <div class="flex gap-3 border-t border-slate-100 pt-4">
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">{{ __('Save') }}</button>
        <a href="{{ route("admin.{$type}.index") }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
