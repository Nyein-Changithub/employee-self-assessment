<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $activePeriods = AssessmentPeriod::active()->count();

        return view('admin.dashboard', [
            'stats' => [
                ['key' => 'periods', 'label' => 'Assessment Periods', 'value' => AssessmentPeriod::count(), 'hint' => $activePeriods, 'route' => 'admin.periods.index'],
                ['key' => 'submissions', 'label' => 'Submissions', 'value' => Assessment::where('status', 'submitted')->count(), 'route' => 'admin.assessments.index'],
                ['key' => 'employees', 'label' => 'Employees', 'value' => User::where('role', 'employee')->count(), 'route' => 'admin.assessments.index'],
                ['key' => 'departments', 'label' => 'Departments', 'value' => Department::count(), 'route' => 'admin.departments.index'],
                ['key' => 'positions', 'label' => 'Positions', 'value' => Position::count(), 'route' => 'admin.positions.index'],
            ],
            // Submitted only, newest first; users and periods are eager-loaded in two extra queries (no N+1).
            'recent' => Assessment::with(['user:id,name,department', 'period:id,title'])
                ->where('status', 'submitted')
                ->orderByDesc('submitted_at')->orderByDesc('id')
                ->limit(5)->get(),
            // Active periods with their *submitted* counts (drafts excluded).
            'periods' => AssessmentPeriod::active()->withCount('submittedAssessments')->latest()->limit(5)->get(),
            'activePeriods' => $activePeriods,
        ]);
    }
}
