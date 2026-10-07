@extends('layouts.admin')
@php
    $breadcrumbs = [[__('Home'), route('admin.dashboard')], [__($labels['plural']), route("admin.{$type}.index")], [$item->name_en, null]];
@endphp
@section('title', __($labels['details']))
@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <a href="{{ route("admin.{$type}.index") }}" class="text-sm text-slate-500 hover:text-slate-800">← {{ __('Back to list') }}</a>
        <h1 class="mt-1 text-xl font-semibold">{{ __($labels['details']) }}</h1>
    </div>
    <div class="flex gap-2">
        <a href="{{ route("admin.{$type}.edit", $item->id) }}" class="rounded-lg border border-indigo-300 px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50">{{ __('Edit') }}</a>
        <form method="POST" action="{{ route("admin.{$type}.destroy", $item->id) }}"
              data-confirm
              data-confirm-title="{{ __('Delete this item?') }}"
              data-confirm-item="{{ $item->name_en }}"
              data-confirm-message="{{ __('This cannot be undone. Existing submissions keep the value they already saved.') }}"
              data-confirm-ok="{{ __('Yes, delete') }}">
            @csrf @method('DELETE')
            <button class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
        </form>
    </div>
</div>

<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <dl class="grid gap-x-8 gap-y-5 text-sm sm:grid-cols-2">
        <div><dt class="text-slate-500">{{ __('ID') }}</dt><dd class="mt-1 font-medium">{{ $item->id }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Status') }}</dt>
            <dd class="mt-1"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">{{ $item->is_active ? __('Active') : __('Inactive') }}</span></dd></div>
        <div><dt class="text-slate-500">{{ __('Name (English)') }}</dt><dd class="mt-1 font-medium">{{ $item->name_en }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Name (Myanmar)') }}</dt><dd class="mt-1 font-medium">{{ $item->name_mm }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Created At') }}</dt><dd class="mt-1">{{ $item->created_at?->format('Y-m-d H:i') }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Last updated') }}</dt><dd class="mt-1">{{ $item->updated_at?->format('Y-m-d H:i') }}</dd></div>
        <div><dt class="text-slate-500">{{ __('Employees using this') }}</dt><dd class="mt-1 font-medium">{{ $usage }}</dd></div>
    </dl>
</div>
@endsection
