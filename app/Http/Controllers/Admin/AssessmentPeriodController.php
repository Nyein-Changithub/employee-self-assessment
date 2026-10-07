<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentPeriodController extends Controller
{
    public function index(): View
    {
        return view('admin.periods.index', [
            'periods' => AssessmentPeriod::withCount(['questions', 'assessments'])->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:assessment_periods,slug'],
        ]);

        AssessmentPeriod::create($data + ['is_active' => true]);

        return back()->with('status', __('Period created.'));
    }
}
