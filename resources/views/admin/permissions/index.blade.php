@extends('layouts.admin')
@php
    $breadcrumbs = [[__('Home'), route('admin.dashboard')], [__('Permissions'), null]];
    $sortLink = fn (string $col) => request()->fullUrlWithQuery(['sort' => $col, 'dir' => $sort === $col && $dir === 'asc' ? 'desc' : 'asc', 'page' => null]);
    $arrow = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '↑' : '↓') : '';
    $filtered = $search !== '';
@endphp
@section('title', __('Permissions'))
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">{{ __('Permissions') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Showing :from to :to of :total results', ['from' => $permissions->firstItem() ?? 0, 'to' => $permissions->lastItem() ?? 0, 'total' => $permissions->total()]) }}</p>
    </div>
    <a href="{{ route('admin.permissions.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1z"/></svg>
        {{ __('Add Permission') }}
    </a>
</div>
@include('partials.errors')

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    @include('partials.table-toolbar', compact('sort', 'dir', 'search', 'perPage', 'filtered'))

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr><th class="px-4 py-3"><a href="{{ $sortLink('id') }}" class="hover:text-slate-800">{{ __('ID') }} {{ $arrow('id') }}</a></th><th class="px-4 py-3"><a href="{{ $sortLink('name') }}" class="hover:text-slate-800">{{ __('Name') }} {{ $arrow('name') }}</a></th><th class="px-4 py-3">{{ __('Guard Name') }}</th><th class="px-4 py-3">{{ __('Roles') }}</th><th class="px-4 py-3"><a href="{{ $sortLink('created_at') }}" class="hover:text-slate-800">{{ __('Created At') }} {{ $arrow('created_at') }}</a></th><th class="px-4 py-3 text-right">{{ __('Actions') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($permissions as $permission)
                @php $builtIn = \App\Support\Rbac::isBuiltInPermission($permission->name); @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-500">{{ $permission->id }}</td>
                    <td class="px-4 py-3 font-mono font-medium">{{ $permission->name }} @if ($builtIn)<span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 font-sans text-xs font-normal text-slate-500">{{ __('Built-in') }}</span>@endif</td>
                    <td class="px-4 py-3 text-slate-500">{{ $permission->guard_name }}</td>
                    <td class="px-4 py-3">{{ $permission->roles_count }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $permission->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex justify-end gap-2">
                            @unless ($builtIn)
                                <a href="{{ route('admin.permissions.edit', $permission) }}" class="rounded-lg border border-indigo-300 px-3 py-1 text-indigo-700 hover:bg-indigo-50">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}"
                                  data-confirm
                                  data-confirm-title="{{ __('Delete this item?') }}"
                                  data-confirm-item="{{ $permission->name }}"
                                  data-confirm-message="{{ __('This cannot be undone. The permission will be removed from every role that has it.') }}"
                                  data-confirm-ok="{{ __('Yes, delete') }}">
                                @csrf @method('DELETE')
                                <button class="rounded-lg border border-red-300 px-3 py-1 text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                            </form>
                            @else
                                <span class="px-3 py-1 text-xs text-slate-400">{{ __('Locked') }}</span>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center"><div class="font-medium text-slate-700">{{ __('No results found.') }}</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($permissions->hasPages())<div class="border-t border-slate-200 p-4">{{ $permissions->links() }}</div>@endif
</div>
@endsection
