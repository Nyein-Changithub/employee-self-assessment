@props(['items' => []])

{{-- Each item: [label, url|null]. The last item is the current page. Labels are translated by the caller. --}}
@if (count($items))
    <nav aria-label="{{ __('Breadcrumb') }}" class="print:hidden">
        <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-sm text-slate-500">
            @foreach ($items as [$label, $url])
                @php $last = $loop->last; @endphp
                <li class="flex min-w-0 items-center gap-1.5">
                    @if ($loop->first)
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12l9-9 9 9M5 10v10h14V10"/></svg>
                    @else
                        <svg class="h-4 w-4 shrink-0 text-slate-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7.3 4.3a1 1 0 0 1 1.4 0l5 5a1 1 0 0 1 0 1.4l-5 5a1 1 0 0 1-1.4-1.4L11.6 10 7.3 5.7a1 1 0 0 1 0-1.4z"/></svg>
                    @endif

                    @if ($url && ! $last)
                        <a href="{{ $url }}" class="max-w-[9rem] truncate hover:text-indigo-600 sm:max-w-xs">{{ $label }}</a>
                    @else
                        <span @if ($last) aria-current="page" @endif class="max-w-[11rem] truncate {{ $last ? 'font-medium text-slate-800' : '' }} sm:max-w-sm">{{ $label }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
