<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCycle;
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
                ['label' => 'Cycles', 'value' => AssessmentCycle::count(), 'route' => 'admin.cycles.index'],
                ['label' => 'Submissions', 'value' => Assessment::where('status', 'submitted')->count(), 'route' => 'admin.assessments.index'],
                ['label' => 'Employees', 'value' => User::where('role', 'employee')->count(), 'route' => 'admin.assessments.index'],
                ['label' => 'Departments', 'value' => Department::count(), 'route' => 'admin.departments.index'],
                ['label' => 'Positions', 'value' => Position::count(), 'route' => 'admin.positions.index'],
            ],
            'recent' => Assessment::with(['user', 'cycle'])->where('status', 'submitted')->latest('submitted_at')->limit(5)->get(),
            'cycles' => AssessmentCycle::withCount('assessments')->latest()->limit(5)->get(),
        ]);
    }
}
