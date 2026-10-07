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
                <dd class="font-medium break-words">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
</section>

@foreach ($answers as $answer)
    <section class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-medium">{{ $loop->iteration }}. {{ $answer->question->text() }}</h3>
        <div class="mt-3 rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 whitespace-pre-line break-words">{{ $answer->answer_text }}</div>
    </section>
@endforeach
