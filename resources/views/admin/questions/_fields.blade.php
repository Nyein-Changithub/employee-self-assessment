@php $cls = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm'; @endphp
<div class="grid sm:grid-cols-2 gap-3">
    <div><label class="block text-sm font-medium mb-1">{{ __('Question (English)') }}</label>
        <textarea name="question_en" rows="3" required placeholder="e.g. What were your key achievements this quarter?" class="{{ $cls }}">{{ $q ? $q->question_en : old('question_en') }}</textarea></div>
    <div><label class="block text-sm font-medium mb-1">{{ __('Question (Myanmar)') }}</label>
        <textarea name="question_mm" rows="3" required placeholder="ဥပမာ - ဤသုံးလပတ်အတွင်း သင်၏ အဓိကအောင်မြင်မှုများမှာ အဘယ်နည်း။" class="{{ $cls }}">{{ $q ? $q->question_mm : old('question_mm') }}</textarea></div>
</div>
<div class="flex flex-wrap items-center gap-4">
    <div><label class="text-sm font-medium mr-2">{{ __('Order') }}</label>
        <input type="number" min="1" name="order_no" value="{{ $q ? $q->order_no : old('order_no', $nextOrder) }}" placeholder="{{ $nextOrder ?? 1 }}" class="w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
    <label class="text-sm flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" @checked($q ? $q->is_active : true)> {{ __('Active') }}
    </label>
</div>
