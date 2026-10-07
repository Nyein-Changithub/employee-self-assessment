<form method="GET" class="flex flex-wrap items-center gap-3 border-b border-slate-200 p-4">
    <input type="hidden" name="sort" value="{{ $sort }}"><input type="hidden" name="dir" value="{{ $dir }}">
    <div class="relative min-w-[14rem] flex-1">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input type="search" name="search" value="{{ $search }}" placeholder="{{ $placeholder ?? __('Search by name...') }}"
               class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    {{ $slot ?? '' }}
    <select name="per_page" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" aria-label="{{ __('Per page') }}">
        @foreach ([10, 25, 50] as $n)<option value="{{ $n }}" @selected($perPage === $n)>{{ $n }} / {{ __('page') }}</option>@endforeach
    </select>
    <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-900">{{ __('Search') }}</button>
    @if (! empty($filtered))
        <a href="{{ url()->current() }}" class="text-sm text-slate-500 hover:text-slate-800 hover:underline">{{ __('Reset') }}</a>
    @endif
</form>
