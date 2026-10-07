@php
    $cls = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500';
    $builtIn = $role && \App\Support\Rbac::isBuiltInRole($role->name);
    $selected = old('permissions', $role?->permissions->pluck('name')->all() ?? []);
@endphp
<div class="max-w-md">
    <label for="name" class="mb-1 block text-sm font-medium">{{ __('Name') }} <span class="text-red-500">*</span></label>
    <input id="name" name="name" required value="{{ old('name', $role?->name) }}" placeholder="{{ __('e.g. HR Manager') }}" class="{{ $cls }} {{ $builtIn ? 'bg-slate-100 text-slate-500' : '' }}" @readonly($builtIn)>
    @if ($builtIn)<p class="mt-1 text-xs text-slate-500">{{ __('Built-in role names cannot be changed.') }}</p>@endif
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div x-data="{ all() { $root.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true) }, none() { $root.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false) } }">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-sm font-semibold">{{ __('Permissions') }}</h2>
        <div class="flex gap-3 text-xs">
            <button type="button" @click="all()" class="text-indigo-600 hover:underline">{{ __('Select all') }}</button>
            <button type="button" @click="none()" class="text-slate-500 hover:underline">{{ __('Clear') }}</button>
        </div>
    </div>
    @error('permissions.*')<p class="mb-2 text-sm text-red-600">{{ $message }}</p>@enderror
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($groups as $group => $perms)
            <fieldset class="rounded-lg border border-slate-200 p-4">
                <legend class="px-1 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __($group) }}</legend>
                <div class="space-y-2">
                    @foreach ($perms as $perm)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" @checked(in_array($perm->name, $selected, true))>
                            <span class="font-mono">{{ $perm->name }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
    </div>
</div>
