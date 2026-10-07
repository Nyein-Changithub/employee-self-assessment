<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AssessmentsExport;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('admin.assessments.index', [
            'assessments' => AssessmentsExport::baseQuery($filters)->paginate(20)->withQueryString(),
            'periods' => AssessmentPeriod::orderBy('title')->get(),
            'departments' => User::whereNotNull('department')->distinct()->orderBy('department')->pluck('department'),
            'filters' => $filters,
        ]);
    }

    public function show(Assessment $assessment): View
    {
        return view('admin.assessments.show', $this->detail($assessment));
    }

    public function pdf(Assessment $assessment)
    {
        return Pdf::loadView('admin.assessments.pdf', $this->detail($assessment))
            ->download("assessment-{$assessment->id}.pdf");
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        return Excel::download(new AssessmentsExport($this->filters($request)), 'assessments.xlsx');
    }

    public function exportCsv(Request $request): BinaryFileResponse
    {
        return Excel::download(new AssessmentsExport($this->filters($request)), 'assessments.csv', ExcelFormat::CSV);
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'period' => ['nullable', 'integer'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function detail(Assessment $assessment): array
    {
        $assessment->load(['user', 'period', 'answers.question']);

        return [
            'assessment' => $assessment,
            'answers' => $assessment->answers->sortBy(fn ($a) => [$a->question->order_no, $a->question_id]),
        ];
    }
}
