<div class="inline-flex rounded-full border border-slate-300 overflow-hidden text-xs" role="group">
    <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 {{ app()->getLocale() === 'en' ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-slate-50' }}">English</a>
    <a href="{{ route('lang.switch', 'mm') }}" class="px-3 py-1 {{ app()->getLocale() === 'mm' ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-slate-50' }}">မြန်မာ</a>
</div>
