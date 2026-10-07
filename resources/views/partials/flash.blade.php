@if (session('status'))
    <div data-autodismiss role="status" class="flex items-center justify-between gap-3 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm transition-opacity duration-500 print:hidden">
        <span>{{ session('status') }}</span>
        <button type="button" data-close aria-label="{{ __('Close') }}" class="shrink-0 rounded p-1 opacity-60 hover:opacity-100 hover:bg-black/5">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M4.3 4.3a1 1 0 0 1 1.4 0L10 8.6l4.3-4.3a1 1 0 1 1 1.4 1.4L11.4 10l4.3 4.3a1 1 0 0 1-1.4 1.4L10 11.4l-4.3 4.3a1 1 0 0 1-1.4-1.4L8.6 10 4.3 5.7a1 1 0 0 1 0-1.4z"/></svg>
        </button>
    </div>
@endif
