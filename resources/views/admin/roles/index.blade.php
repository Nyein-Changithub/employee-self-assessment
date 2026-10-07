@extends('layouts.admin')
@php
    $breadcrumbs = [[__('Home'), route('admin.dashboard')], [__('Roles'), null]];
    $sortLink = fn (string $col) => request()->fullUrlWithQuery(['sort' => $col, 'dir' => $sort === $col && $dir === 'asc' ? 'desc' : 'asc', 'page' => null]);
    $arrow = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '↑' : '↓') : '';
    $filtered = $search !== '';
@endphp
@section('title', __('Roles'))
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">{{ __('Roles') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Showing :from to :to of :total results', ['from' => $roles->firstItem() ?? 0, 'to' => $roles->lastItem() ?? 0, 'total' => $roles->total()]) }}</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1z"/></svg>
        {{ __('Add Role') }}
    </a>
</div>
@include('partials.errors')

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    @include('partials.table-toolbar', compact('sort', 'dir', 'search', 'perPage', 'filtered'))

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr><th class="px-4 py-3"><a href="{{ $sortLink('id') }}" class="hover:text-slate-800">{{ __('ID') }} {{ $arrow('id') }}</a></th><th class="px-4 py-3"><a href="{{ $sortLink('name') }}" class="hover:text-slate-800">{{ __('Name') }} {{ $arrow('name') }}</a></th><th class="px-4 py-3">{{ __('Guard Name') }}</th><th class="px-4 py-3">{{ __('Permissions') }}</th><th class="px-4 py-3">{{ __('Users') }}</th><th class="px-4 py-3"><a href="{{ $sortLink('created_at') }}" class="hover:text-slate-800">{{ __('Created At') }} {{ $arrow('created_at') }}</a></th><th class="px-4 py-3 text-right">{{ __('Actions') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($roles as $role)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-500">{{ $role->id }}</td>
                    <td class="px-4 py-3 font-medium">{{ \App\Support\Rbac::isBuiltInRole($role->name) ? mb_strtoupper($role->name) : $role->name }} @if (\App\Support\Rbac::isBuiltInRole($role->name))<span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs font-normal text-slate-500">{{ __('Built-in') }}</span>@endif</td>
                    <td class="px-4 py-3 text-slate-500">{{ $role->guard_name }}</td>
                    <td class="px-4 py-3">{{ $role->name === 'admin' ? __('All permissions') : trans_choice('{1} :count permission|[2,*] :count permissions', $role->permissions_count, ['count' => $role->permissions_count]) }}</td>
                    <td class="px-4 py-3">{{ $role->users_count }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $role->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex justify-end gap-2">
                            @if ($role->name !== 'admin' || auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.roles.edit', $role) }}" class="rounded-lg border border-indigo-300 px-3 py-1 text-indigo-700 hover:bg-indigo-50">{{ __('Edit') }}</a>
                            @endif
                            @unless (\App\Support\Rbac::isBuiltInRole($role->name))
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                  data-confirm
                                  data-confirm-title="{{ __('Delete this item?') }}"
                                  data-confirm-item="{{ $role->name }}"
                                  data-confirm-message="{{ __('This cannot be undone. Users must be reassigned before a role can be deleted.') }}"
                                  data-confirm-ok="{{ __('Yes, delete') }}">
                                @csrf @method('DELETE')
                                <button class="rounded-lg border border-red-300 px-3 py-1 text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                            </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-12 text-center"><div class="font-medium text-slate-700">{{ __('No results found.') }}</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($roles->hasPages())<div class="border-t border-slate-200 p-4">{{ $roles->links() }}</div>@endif
</div>
@endsection
