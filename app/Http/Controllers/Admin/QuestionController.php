<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPeriod;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(AssessmentPeriod $period): View
    {
        return view('admin.questions.index', [
            'period' => $period,
            'questions' => $period->questions,
            'nextOrder' => ($period->questions()->max('order_no') ?? 0) + 1,
        ]);
    }

    public function store(Request $request, AssessmentPeriod $period): RedirectResponse
    {
        $data = $this->validated($request, $period->id);
        $data['order_no'] ??= ($period->questions()->max('order_no') ?? 0) + 1;

        $period->questions()->create($data);

        return back()->with('status', __('Question added.'));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $question->update($this->validated($request, $question->assessment_period_id, $question->id));

        return back()->with('status', __('Question updated.'));
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return back()->with('status', __('Question deleted.'));
    }

    private function validated(Request $request, int $periodId, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'question_en' => ['required', 'string', 'max:5000'],
            'question_mm' => ['required', 'string', 'max:5000'],
            'order_no' => [
                'nullable', 'integer', 'min:1',
                Rule::unique('questions', 'order_no')->where('assessment_period_id', $periodId)->ignore($ignoreId),
            ],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'order_no.unique' => __('This order number is already used by another question.'),
        ]);

        // Checkbox: absent means off. Applies to update; new questions default to active.
        $data['is_active'] = $request->boolean('is_active', $request->isMethod('post'));

        return $data;
    }
}
