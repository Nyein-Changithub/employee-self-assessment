<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentCycle;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function show(string $slug): View
    {
        $cycle = AssessmentCycle::where('slug', $slug)->firstOrFail();

        // Read-only view: only for the assessment this browser session submitted.
        $assessmentId = session('submitted_assessment_id');
        if ($assessmentId) {
            $assessment = Assessment::with(['user', 'answers.question'])
                ->where('id', $assessmentId)
                ->where('assessment_cycle_id', $cycle->id)
                ->where('status', 'submitted')
                ->first();

            if ($assessment) {
                return view('assessment.readonly', [
                    'cycle' => $cycle,
                    'assessment' => $assessment,
                    'answers' => $assessment->answers->sortBy('question.order_no'),
                ]);
            }
        }

        abort_unless($cycle->is_active, 404);

        return view('assessment.form', [
            'cycle' => $cycle,
            'questions' => $cycle->questions()->where('is_active', true)->get(),
        ]);
    }

    public function submit(Request $request, string $slug): RedirectResponse
    {
        $cycle = AssessmentCycle::where('slug', $slug)->firstOrFail();
        $questions = $cycle->questions()->where('is_active', true)->get();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'answers' => ['required', 'array'],
        ];
        foreach ($questions as $question) {
            $rules["answers.{$question->id}"] = ['required', 'string', 'max:10000'];
        }
        $data = $request->validate($rules);

        $user = $this->resolveUser($data);

        $existing = Assessment::where('user_id', $user->id)
            ->where('assessment_cycle_id', $cycle->id)
            ->where('status', 'submitted')
            ->first();

        if ($existing) {
            session(['submitted_assessment_id' => $existing->id]);

            return redirect()->route('assessment.show', $slug)
                ->with('already_submitted', true);
        }

        abort_unless($cycle->is_active, 404);

        try {
            $assessment = DB::transaction(function () use ($user, $cycle, $questions, $data) {
                $assessment = Assessment::create([
                    'assessment_cycle_id' => $cycle->id,
                    'user_id' => $user->id,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);

                foreach ($questions as $question) {
                    $assessment->answers()->create([
                        'question_id' => $question->id,
                        'answer_text' => $data['answers'][$question->id],
                    ]);
                }

                return $assessment;
            });
        } catch (UniqueConstraintViolationException) {
            // Concurrent double-submit: the other request won.
            $assessment = Assessment::where('user_id', $user->id)
                ->where('assessment_cycle_id', $cycle->id)->firstOrFail();
            session(['submitted_assessment_id' => $assessment->id]);

            return redirect()->route('assessment.show', $slug)->with('already_submitted', true);
        }

        session(['submitted_assessment_id' => $assessment->id]);

        return redirect()->route('assessment.show', $slug)->with('just_submitted', true);
    }

    /**
     * Find or create the employee by employee_id / email.
     * Never touches privileged accounts, and refuses ambiguous ID/email pairs
     * so one person cannot attach to (or reveal) another employee's record.
     */
    private function resolveUser(array $data): User
    {
        $byId = User::where('employee_id', $data['employee_id'])->first();
        $byEmail = User::where('email', $data['email'])->first();

        if (($byId && $byEmail && $byId->isNot($byEmail)) || ($byId && $byId->email !== $data['email'])
            || ($byEmail && $byEmail->employee_id !== null && $byEmail->employee_id !== $data['employee_id'])) {
            throw ValidationException::withMessages([
                'employee_id' => __('This Employee ID and email do not match our records.'),
            ]);
        }

        $user = $byId ?? $byEmail;

        if ($user && $user->role !== 'employee') {
            throw ValidationException::withMessages([
                'email' => __('This email cannot be used for the assessment form.'),
            ]);
        }

        $profile = [
            'name' => $data['name'],
            'employee_id' => $data['employee_id'],
            'email' => $data['email'],
            'position' => $data['position'],
            'department' => $data['department'],
        ];

        return $user
            ? tap($user)->update($profile)
            : User::create($profile + ['role' => 'employee']);
    }
}
