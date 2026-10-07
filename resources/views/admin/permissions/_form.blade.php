@php $cls = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500'; @endphp
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium">{{ __('Name') }} <span class="text-red-500">*</span></label>
        <input id="name" name="name" required value="{{ old('name', $permission?->name) }}" placeholder="e.g. reports.view" class="{{ $cls }} font-mono">
        <p class="mt-1 text-xs text-slate-500">{{ __('Use lowercase letters, numbers, dots, dashes or underscores only.') }}</p>
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="guard_name" class="mb-1 block text-sm font-medium">{{ __('Guard Name') }} <span class="text-red-500">*</span></label>
        <select id="guard_name" name="guard_name" class="{{ $cls }}">
            @foreach ($guards as $g)<option value="{{ $g }}" @selected(old('guard_name', $permission?->guard_name ?? 'web') === $g)>{{ $g }}</option>@endforeach
        </select>
        @error('guard_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
