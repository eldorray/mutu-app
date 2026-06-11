<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdiwiyataIndicator extends Model
{
    protected $guarded = [];

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(AdiwiyataInstrument::class, 'instrument_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(AdiwiyataComponent::class, 'component_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(AdiwiyataIndicatorEvidence::class, 'indicator_id')->orderBy('sort_order');
    }
}
