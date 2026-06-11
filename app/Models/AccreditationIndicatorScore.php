<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationIndicatorScore extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_na' => 'boolean',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AccreditationCycle::class, 'cycle_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AccreditationIndicator::class, 'indicator_id');
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(AccreditationRubric::class, 'rubric_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
