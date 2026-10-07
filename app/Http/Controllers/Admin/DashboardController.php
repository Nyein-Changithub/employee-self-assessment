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
        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Assessment Periods', 'value' => AssessmentPeriod::count(), 'route' => 'admin.periods.index'],
                ['label' => 'Submissions', 'value' => Assessment::where('status', 'submitted')->count(), 'route' => 'admin.assessments.index'],
                ['label' => 'Employees', 'value' => User::where('role', 'employee')->count(), 'route' => 'admin.assessments.index'],
                ['label' => 'Departments', 'value' => Department::count(), 'route' => 'admin.departments.index'],
                ['label' => 'Positions', 'value' => Position::count(), 'route' => 'admin.positions.index'],
            ],
            'recent' => Assessment::with(['user', 'period'])->where('status', 'submitted')->latest('submitted_at')->limit(5)->get(),
            'periods' => AssessmentPeriod::withCount('assessments')->latest()->limit(5)->get(),
        ]);
    }
}
