@extends('layouts.admin')
@section('title', __($labels['plural']))
@section('content')
@php
    $sortLink = fn (string $col) => request()->fullUrlWithQuery([
        'sort' => $col,
        'dir' => $sort === $col && $dir === 'asc' ? 'desc' : 'asc',
        'page' => null,
    ]);
    $arrow = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '↑' : '↓') : '';
    $filtered = $search !== '' || $status;
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">{{ __($labels['plural']) }}</h1>
        <p class="text-sm text-slate-500">{{ __('Showing :from to :to of :total results', ['from' => $items->firstItem() ?? 0, 'to' => $items->lastItem() ?? 0, 'total' => $items->total()]) }}</p>
    </div>
    <a href="{{ route("admin.{$type}.create") }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1z"/></svg>
        {{ __($labels['add']) }}
    </a>
</div>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-slate-200 p-4">
        <input type="hidden" name="sort" value="{{ $sort }}"><input type="hidden" name="dir" value="{{ $dir }}">
        <div class="relative min-w-[14rem] flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search by name...') }}"
                   class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <select name="status" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">{{ __('All statuses') }}</option>
            <option value="active" @selected($status === 'active')>{{ __('Active') }}</option>
            <option value="inactive" @selected($status === 'inactive')>{{ __('Inactive') }}</option>
        </select>
        <select name="per_page" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" aria-label="{{ __('Per page') }}">
            @foreach ([10, 25, 50] as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }} / {{ __('page') }}</option>@endforeach
        </select>
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-900">{{ __('Search') }}</button>
        @if ($filtered)
            <a href="{{ route("admin.{$type}.index") }}" class="text-sm text-slate-500 hover:text-slate-800 hover:underline">{{ __('Reset') }}</a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3"><a href="{{ $sortLink('id') }}" class="hover:text-slate-800">{{ __('ID') }} {{ $arrow('id') }}</a></th>
                    <th class="px-4 py-3"><a href="{{ $sortLink('name_en') }}" class="hover:text-slate-800">{{ __('Name') }} {{ $arrow('name_en') }}</a></th>
                    <th class="px-4 py-3">{{ __('Myanmar Name') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3"><a href="{{ $sortLink('created_at') }}" class="hover:text-slate-800">{{ __('Created At') }} {{ $arrow('created_at') }}</a></th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($items as $item)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-500">{{ $item->id }}</td>
                    <td class="px-4 py-3 font-medium">{{ $item->name_en }}</td>
                    <td class="px-4 py-3">{{ $item->name_mm }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $item->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route("admin.{$type}.show", $item->id) }}" class="rounded-lg border border-slate-300 px-3 py-1 hover:bg-slate-100">{{ __('View') }}</a>
                            <a href="{{ route("admin.{$type}.edit", $item->id) }}" class="rounded-lg border border-indigo-300 px-3 py-1 text-indigo-700 hover:bg-indigo-50">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route("admin.{$type}.destroy", $item->id) }}"
                                  data-confirm
                                  data-confirm-title="{{ __('Delete this item?') }}"
                                  data-confirm-item="{{ $item->name_en }}"
                                  data-confirm-message="{{ __('This cannot be undone. Existing submissions keep the value they already saved.') }}"
                                  data-confirm-ok="{{ __('Yes, delete') }}">
                                @csrf @method('DELETE')
                                <button class="rounded-lg border border-red-300 px-3 py-1 text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center">
                        <div class="font-medium text-slate-700">{{ __('No results found.') }}</div>
                        @if ($filtered)<div class="mt-1 text-sm text-slate-500">{{ __('Try changing your search or filter.') }}</div>@endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <div class="border-t border-slate-200 p-4">{{ $items->links() }}</div>
    @endif
</div>
@endsection
