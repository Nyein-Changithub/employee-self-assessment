<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentCycle;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(AssessmentCycle $cycle): View
    {
        return view('admin.questions.index', [
            'cycle' => $cycle,
            'questions' => $cycle->questions,
        ]);
    }

    public function store(Request $request, AssessmentCycle $cycle): RedirectResponse
    {
        $data = $this->validated($request);
        $data['order_no'] ??= ($cycle->questions()->max('order_no') ?? 0) + 1;

        $cycle->questions()->create($data);

        return back()->with('status', __('Question added.'));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $question->update($this->validated($request));

        return back()->with('status', __('Question updated.'));
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return back()->with('status', __('Question deleted.'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question_en' => ['required', 'string', 'max:5000'],
            'question_mm' => ['required', 'string', 'max:5000'],
            'guide_en' => ['nullable', 'string', 'max:5000'],
            'guide_mm' => ['nullable', 'string', 'max:5000'],
            'order_no' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Checkbox: absent means off. Applies to update; new questions default to active.
        $data['is_active'] = $request->boolean('is_active', $request->isMethod('post'));

        return $data;
    }
}
