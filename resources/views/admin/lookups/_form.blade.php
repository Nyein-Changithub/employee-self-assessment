@php
    $cls = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500';
    $active = $errors->any() ? (bool) old('is_active') : ($item?->is_active ?? true);
@endphp
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="name_en" class="mb-1 block text-sm font-medium">{{ __('Name (English)') }} <span class="text-red-500">*</span></label>
        <input id="name_en" name="name_en" required value="{{ old('name_en', $item?->name_en) }}" placeholder="{{ $config['placeholder_en'] }}" class="{{ $cls }}">
        @error('name_en')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="name_mm" class="mb-1 block text-sm font-medium">{{ __('Name (Myanmar)') }} <span class="text-red-500">*</span></label>
        <input id="name_mm" name="name_mm" required value="{{ old('name_mm', $item?->name_mm) }}" placeholder="{{ $config['placeholder_mm'] }}" class="{{ $cls }}">
        @error('name_mm')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked($active)>
    {{ __('Active') }} <span class="text-slate-400">({{ __('shown in the employee form dropdown') }})</span>
</label>
