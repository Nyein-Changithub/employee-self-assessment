@php $cls = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm'; @endphp
<div class="grid sm:grid-cols-2 gap-3">
    <div><label class="block text-sm font-medium mb-1">{{ __('Question (English)') }}</label>
        <textarea name="question_en" rows="2" required class="{{ $cls }}">{{ $q?->question_en }}</textarea></div>
    <div><label class="block text-sm font-medium mb-1">{{ __('Question (Myanmar)') }}</label>
        <textarea name="question_mm" rows="2" required class="{{ $cls }}">{{ $q?->question_mm }}</textarea></div>
    <div><label class="block text-sm font-medium mb-1">{{ __('Guide (English)') }}</label>
        <textarea name="guide_en" rows="2" class="{{ $cls }}">{{ $q?->guide_en }}</textarea></div>
    <div><label class="block text-sm font-medium mb-1">{{ __('Guide (Myanmar)') }}</label>
        <textarea name="guide_mm" rows="2" class="{{ $cls }}">{{ $q?->guide_mm }}</textarea></div>
</div>
<div class="flex flex-wrap items-center gap-4">
    <div><label class="text-sm font-medium mr-2">{{ __('Order') }}</label>
        <input type="number" min="0" name="order_no" value="{{ $q?->order_no }}" class="w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
    <label class="text-sm flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" @checked($q ? $q->is_active : true)> {{ __('Active') }}
    </label>
</div>
