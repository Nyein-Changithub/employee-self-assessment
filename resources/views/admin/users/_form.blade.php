@php
    $cls = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500';
    $currentRole = old('role', $user?->roles->first()?->name);
    $isSelf = $user && $user->is(auth()->user());
@endphp
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium">{{ __('Full Name') }} <span class="text-red-500">*</span></label>
        <input id="name" name="name" required value="{{ old('name', $user?->name) }}" placeholder="{{ __('e.g. Aung Aung') }}" class="{{ $cls }}">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email" class="mb-1 block text-sm font-medium">{{ __('Email') }} <span class="font-normal text-slate-400">({{ __('required for roles') }})</span></label>
        <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" placeholder="name@company.com" class="{{ $cls }}">
        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="mb-1 block text-sm font-medium">{{ __('Password') }} @if ($user)<span class="font-normal text-slate-400">({{ __('leave blank to keep current') }})</span>@endif</label>
        <input id="password" name="password" type="password" autocomplete="new-password" class="{{ $cls }}">
        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_confirmation" class="mb-1 block text-sm font-medium">{{ __('Confirm Password') }}</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="{{ $cls }}">
    </div>
    <div>
        <label for="employee_id" class="mb-1 block text-sm font-medium">{{ __('Employee ID') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
        <input id="employee_id" name="employee_id" value="{{ old('employee_id', $user?->employee_id) }}" placeholder="{{ __('e.g. EMP-0001') }}" class="{{ $cls }}">
        @error('employee_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="role" class="mb-1 block text-sm font-medium">{{ __('Assigned Role') }}</label>
        <select id="role" name="role" class="{{ $cls }}" @disabled($isSelf)>
            <option value="">{{ __('Employee (no admin access)') }}</option>
            @foreach ($roles as $r)
                @continue($r === 'admin' && ! auth()->user()->hasRole('admin'))
                <option value="{{ $r }}" @selected($currentRole === $r)>{{ $r }}</option>
            @endforeach
        </select>
        @if ($isSelf)
            <input type="hidden" name="role" value="{{ $user->roles->first()?->name }}">
            <p class="mt-1 text-xs text-slate-500">{{ __('You cannot change your own role.') }}</p>
        @endif
        @error('role')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="department" class="mb-1 block text-sm font-medium">{{ __('Department') }}</label>
        <select id="department" name="department" class="{{ $cls }}">
            <option value="">{{ __('Select department') }}</option>
            @foreach ($departments as $d)<option value="{{ $d->name_en }}" @selected(old('department', $user?->department) === $d->name_en)>{{ $d->label() }}</option>@endforeach
        </select>
        @error('department')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="position" class="mb-1 block text-sm font-medium">{{ __('Position') }}</label>
        <select id="position" name="position" class="{{ $cls }}">
            <option value="">{{ __('Select position') }}</option>
            @foreach ($positions as $p)<option value="{{ $p->name_en }}" @selected(old('position', $user?->position) === $p->name_en)>{{ $p->label() }}</option>@endforeach
        </select>
        @error('position')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
