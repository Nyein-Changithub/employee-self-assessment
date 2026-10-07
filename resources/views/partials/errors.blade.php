@if ($errors->any())
    <div data-autodismiss role="alert" class="flex items-start justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 transition-opacity duration-500">
        <ul class="ml-4 list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" data-close aria-label="{{ __('Close') }}" class="shrink-0 rounded p-1 opacity-60 hover:bg-black/5 hover:opacity-100">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M4.3 4.3a1 1 0 0 1 1.4 0L10 8.6l4.3-4.3a1 1 0 1 1 1.4 1.4L11.4 10l4.3 4.3a1 1 0 0 1-1.4 1.4L10 11.4l-4.3 4.3a1 1 0 0 1-1.4-1.4L8.6 10 4.3 5.7a1 1 0 0 1 0-1.4z"/></svg>
        </button>
    </div>
@endif
