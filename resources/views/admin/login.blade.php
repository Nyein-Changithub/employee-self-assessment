@extends('layouts.app')
@section('title', __('Admin Login'))
@section('content')
<form method="POST" action="{{ route('admin.login.attempt') }}" class="max-w-sm mx-auto bg-white rounded-xl shadow-sm p-6 space-y-4">
    @csrf
    <h1 class="text-xl font-semibold">{{ __('Admin Login') }}</h1>
    <div>
        <label for="email" class="block text-sm font-medium mb-1">{{ __('Email') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="block text-sm font-medium mb-1">{{ __('Password') }}</label>
        <input id="password" name="password" type="password" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> {{ __('Remember me') }}</label>
    <button class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2">{{ __('Log in') }}</button>
</form>
@endsection
