<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationComponent extends Model
{
    protected $guarded = [];

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(AccreditationInstrument::class, 'instrument_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AccreditationItem::class, 'component_id')->orderBy('sort_order');
    }
}
