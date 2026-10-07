<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'assessment_period_id', 'question_en', 'question_mm', 'guide_en', 'guide_mm', 'order_no', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'order_no' => 'integer'];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AssessmentPeriod::class, 'assessment_period_id');
    }

    public function text(): string
    {
        return app()->getLocale() === 'mm' ? $this->question_mm : $this->question_en;
    }

    public function guide(): ?string
    {
        return app()->getLocale() === 'mm' ? $this->guide_mm : $this->guide_en;
    }
}
