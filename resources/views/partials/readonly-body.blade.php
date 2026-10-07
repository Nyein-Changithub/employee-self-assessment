<section class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="font-semibold mb-3">{{ __('Your Details') }}</h2>
    <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        @foreach ([
            'Full Name' => $assessment->user->name,
            'Employee ID' => $assessment->user->employee_id,
            'Email' => $assessment->user->email,
            'Position' => $assessment->user->position,
            'Department' => $assessment->user->department,
        ] as $label => $value)
            <div>
                <dt class="text-slate-500">{{ __($label) }}</dt>
                <dd class="font-medium break-words">{{ $value ?: '—' }}</dd>
            </div>
        @endforeach
    </dl>
</section>

@foreach ($answers as $answer)
    <section class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex gap-3 bg-indigo-50 border-b border-indigo-100 px-5 py-4">
            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">Q{{ $loop->iteration }}</span>
            <p class="font-semibold text-slate-900 break-words">{{ $answer->question->text() }}</p>
        </div>
        <div class="p-5">
            <div class="mb-2 flex items-center gap-2 text-sm font-medium text-emerald-700">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-xs font-semibold text-white">A</span>
                {{ __('Answer') }}
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 px-4 py-3 whitespace-pre-line break-words">{{ $answer->answer_text }}</div>
        </div>
    </section>
@endforeach
