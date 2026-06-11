<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationRubric extends Model
{
    protected $guarded = [];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AccreditationIndicator::class, 'indicator_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(AccreditationRubricLevel::class, 'rubric_level_id');
    }
}
