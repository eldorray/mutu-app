<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdiwiyataComponent extends Model
{
    protected $guarded = [];

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(AdiwiyataInstrument::class, 'instrument_id');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(AdiwiyataIndicator::class, 'component_id')->orderBy('sort_order');
    }
}
