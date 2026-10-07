<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function show(string $slug): View
    {
        $period = AssessmentPeriod::where('slug', $slug)->firstOrFail();

        // Read-only view: only for the assessment this browser session submitted.
        $assessmentId = session('submitted_assessment_id');
        if ($assessmentId) {
            $assessment = Assessment::with(['user', 'answers.question'])
                ->where('id', $assessmentId)
                ->where('assessment_period_id', $period->id)
                ->where('status', 'submitted')
                ->first();

            if ($assessment) {
                return view('assessment.readonly', [
                    'period' => $period,
                    'assessment' => $assessment,
                    'answers' => $assessment->answers->sortBy('question.order_no'),
                ]);
            }
        }

        abort_unless($period->is_active, 404);

        return view('assessment.form', [
            'period' => $period,
            'questions' => $period->questions()->where('is_active', true)->get(),
            'departments' => Department::where('is_active', true)->orderBy('name_en')->get(),
            'positions' => Position::where('is_active', true)->orderBy('name_en')->get(),
        ]);
    }

    public function submit(Request $request, string $slug): RedirectResponse
    {
        $period = AssessmentPeriod::where('slug', $slug)->firstOrFail();
        $questions = $period->questions()->where('is_active', true)->get();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'position' => ['required', Rule::exists('positions', 'name_en')->where('is_active', true)],
            'department' => ['required', Rule::exists('departments', 'name_en')->where('is_active', true)],
            'answers' => ['required', 'array'],
        ];
        foreach ($questions as $question) {
            $rules["answers.{$question->id}"] = ['required', 'string', 'max:10000'];
        }
        $data = $request->validate($rules);

        $user = $this->findUser($data);

        $existing = $user
            ? Assessment::where('user_id', $user->id)
                ->where('assessment_period_id', $period->id)
                ->where('status', 'submitted')
                ->first()
            : null;

        if ($existing) {
            // Email is optional, so Employee ID alone must not unlock a colleague's answers:
            // require the typed name to match the record as well.
            if (mb_strtolower(trim($data['name'])) !== mb_strtolower(trim($user->name))) {
                $this->mismatch();
            }

            session(['submitted_assessment_id' => $existing->id]);

            return redirect()->route('assessment.show', $slug)
                ->with('already_submitted', true);
        }

        abort_unless($period->is_active, 404);

        // Profile is only written once we know this is a genuine new submission.
        $user = $this->saveProfile($user, $data);

        try {
            $assessment = DB::transaction(function () use ($user, $period, $questions, $data) {
                $assessment = Assessment::create([
                    'assessment_period_id' => $period->id,
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
                ->where('assessment_period_id', $period->id)->firstOrFail();
            session(['submitted_assessment_id' => $assessment->id]);

            return redirect()->route('assessment.show', $slug)->with('already_submitted', true);
        }

        session(['submitted_assessment_id' => $assessment->id]);

        return redirect()->route('assessment.show', $slug)->with('just_submitted', true);
    }

    /**
     * Look up an existing employee by employee_id / email without changing anything.
     * Never matches privileged accounts, and refuses ambiguous ID/email pairs
     * so one person cannot attach to another employee's record.
     */
    private function findUser(array $data): ?User
    {
        $email = $data['email'] ?? null;

        $byId = User::where('employee_id', $data['employee_id'])->first();
        $byEmail = $email ? User::where('email', $email)->first() : null;

        if ($byId && $byEmail && $byId->isNot($byEmail)) {
            $this->mismatch();
        }
        // A record that has an email on file requires that email to be entered.
        if ($byId && $byId->email !== null && $byId->email !== $email) {
            $this->mismatch();
        }
        if ($byEmail && ! $byId && $byEmail->employee_id !== null && $byEmail->employee_id !== $data['employee_id']) {
            $this->mismatch();
        }

        $user = $byId ?? $byEmail;

        if ($user && ($user->isStaff() || $user->role !== 'employee')) {
            throw ValidationException::withMessages([
                'employee_id' => __('This Employee ID cannot be used for the assessment form.'),
            ]);
        }

        return $user;
    }

    private function saveProfile(?User $user, array $data): User
    {
        $profile = [
            'name' => $data['name'],
            'position' => $data['position'],
            'department' => $data['department'],
        ];

        if ($user) {
            $user->update($profile + array_filter(['email' => $data['email'] ?? null]));

            return $user;
        }

        return User::create($profile + [
            'employee_id' => $data['employee_id'],
            'email' => $data['email'] ?? null,
            'role' => 'employee',
        ]);
    }

    private function mismatch(): never
    {
        throw ValidationException::withMessages([
            'employee_id' => __('This Employee ID and email do not match our records.'),
        ]);
    }
}
