<?php

namespace App\Exports;

use App\Models\Assessment;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssessmentsExport implements FromQuery, WithHeadings, WithMapping
{
    /** Questions become extra columns only when a single period is selected. */
    private $questions;

    public function __construct(private array $filters = [])
    {
        $this->questions = empty($filters['period'])
            ? collect()
            : Question::where('assessment_period_id', $filters['period'])->orderBy('order_no')->orderBy('id')->get();
    }

    public static function baseQuery(array $filters): Builder
    {
        return Assessment::query()
            ->with(['user', 'period'])
            ->where('status', 'submitted')
            ->when($filters['period'] ?? null, fn ($q, $v) => $q->where('assessment_period_id', $v))
            ->when($filters['department'] ?? null, fn ($q, $v) => $q->whereHas('user', fn ($u) => $u->where('department', $v)))
            ->latest('submitted_at');
    }

    public function query(): Builder
    {
        return self::baseQuery($this->filters)->with('answers');
    }

    public function headings(): array
    {
        return array_merge(
            ['ID', 'Assessment Period', 'Employee ID', 'Name', 'Email', 'Position', 'Department', 'Submitted At'],
            $this->questions->map(fn ($q) => $q->question_en)->all()
        );
    }

    public function map($assessment): array
    {
        $answers = $assessment->answers->keyBy('question_id');

        return array_merge([
            $assessment->id,
            $assessment->period->title,
            $assessment->user->employee_id,
            self::safe($assessment->user->name),
            $assessment->user->email,
            self::safe($assessment->user->position),
            self::safe($assessment->user->department),
            $assessment->submitted_at?->format('Y-m-d H:i'),
        ], $this->questions->map(fn ($q) => self::safe($answers->get($q->id)?->answer_text))->all());
    }

    /** Neutralise spreadsheet formula injection from free-text answers. */
    private static function safe(?string $value): ?string
    {
        return $value !== null && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
