<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentPeriod extends Model
{
    protected $fillable = ['title', 'slug', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order_no')->orderBy('id');
    }

    public function submittedAssessments(): HasMany
    {
        return $this->hasMany(Assessment::class)->where('status', 'submitted');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
