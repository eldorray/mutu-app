<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationIndicator extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_na_allowed' => 'boolean',
            'is_contextual' => 'boolean',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AccreditationItem::class, 'item_id');
    }

    public function rubrics(): HasMany
    {
        return $this->hasMany(AccreditationRubric::class, 'indicator_id');
    }

    public function evidenceSuggestions(): HasMany
    {
        return $this->hasMany(AccreditationIndicatorEvidenceSuggestion::class, 'indicator_id')->orderBy('sort_order');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AccreditationIndicatorScore::class, 'indicator_id');
    }
}
