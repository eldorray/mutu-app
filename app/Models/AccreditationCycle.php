<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationCycle extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(AccreditationInstrument::class, 'instrument_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(AccreditationEvidence::class, 'cycle_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AccreditationIndicatorScore::class, 'cycle_id');
    }
}
