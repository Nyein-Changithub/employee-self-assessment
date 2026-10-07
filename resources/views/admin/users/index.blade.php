@extends('layouts.admin')
@php
    $breadcrumbs = [[__('Home'), route('admin.dashboard')], [__('Users'), null]];
    $sortLink = fn (string $col) => request()->fullUrlWithQuery(['sort' => $col, 'dir' => $sort === $col && $dir === 'asc' ? 'desc' : 'asc', 'page' => null]);
    $arrow = fn (string $col) => $sort === $col ? ($dir === 'asc' ? '↑' : '↓') : '';
@endphp
@section('title', __('Users'))
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">{{ __('Users') }}</h1>
        <p class="text-sm text-slate-500">{{ __('Showing :from to :to of :total results', ['from' => $users->firstItem() ?? 0, 'to' => $users->lastItem() ?? 0, 'total' => $users->total()]) }}</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1z"/></svg>
        {{ __('Add User') }}
    </a>
</div>
@include('partials.errors')

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    @php $filtered = $search !== '' || $roleFilter !== ''; @endphp
    @component('partials.table-toolbar', compact('sort', 'dir', 'search', 'perPage', 'filtered') + ['placeholder' => __('Search name, email or employee ID...')])
        <select name="role" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">{{ __('All roles') }}</option>
            <option value="employee" @selected($roleFilter === 'employee')>{{ __('Employee (no role)') }}</option>
            @foreach ($roles as $r)<option value="{{ $r }}" @selected($roleFilter === $r)>{{ $r }}</option>@endforeach
        </select>
    @endcomponent

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3"><a href="{{ $sortLink('id') }}" class="hover:text-slate-800">{{ __('ID') }} {{ $arrow('id') }}</a></th>
                    <th class="px-4 py-3"><a href="{{ $sortLink('name') }}" class="hover:text-slate-800">{{ __('Name') }} {{ $arrow('name') }}</a></th>
                    <th class="px-4 py-3">{{ __('Email') }}</th>
                    <th class="px-4 py-3">{{ __('Role') }}</th>
                    <th class="px-4 py-3">{{ __('Department') }}</th>
                    <th class="px-4 py-3"><a href="{{ $sortLink('created_at') }}" class="hover:text-slate-800">{{ __('Created At') }} {{ $arrow('created_at') }}</a></th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($users as $user)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-500">{{ $user->id }}</td>
                    <td class="px-4 py-3 font-medium">{{ $user->name }}@if ($user->employee_id)<div class="text-xs font-normal text-slate-500">{{ $user->employee_id }}</div>@endif</td>
                    <td class="px-4 py-3">{{ $user->email ?: '—' }}</td>
                    <td class="px-4 py-3">
                        @forelse ($user->roles as $r)
                            <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium uppercase text-indigo-700">{{ $r->name }}</span>
                        @empty
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium uppercase text-slate-600">{{ __('Employee') }}</span>
                        @endforelse
                    </td>
                    <td class="px-4 py-3">{{ $user->department ?: '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $user->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.users.show', $user) }}" class="rounded-lg border border-slate-300 px-3 py-1 hover:bg-slate-100">{{ __('View') }}</a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg border border-indigo-300 px-3 py-1 text-indigo-700 hover:bg-indigo-50">{{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  data-confirm
                                  data-confirm-title="{{ __('Delete this item?') }}"
                                  data-confirm-item="{{ $user->name }}"
                                  data-confirm-message="{{ __('This cannot be undone. If this person has submitted assessments, those submissions will be deleted too.') }}"
                                  data-confirm-ok="{{ __('Yes, delete') }}">
                                @csrf @method('DELETE')
                                <button class="rounded-lg border border-red-300 px-3 py-1 text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-12 text-center"><div class="font-medium text-slate-700">{{ __('No results found.') }}</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())<div class="border-t border-slate-200 p-4">{{ $users->links() }}</div>@endif
</div>
@endsection
