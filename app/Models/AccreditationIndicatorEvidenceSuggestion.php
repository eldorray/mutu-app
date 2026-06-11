<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationIndicatorEvidenceSuggestion extends Model
{
    protected $guarded = [];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AccreditationIndicator::class, 'indicator_id');
    }
}
