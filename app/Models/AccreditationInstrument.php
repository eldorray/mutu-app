<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationInstrument extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function components(): HasMany
    {
        return $this->hasMany(AccreditationComponent::class, 'instrument_id')->orderBy('sort_order');
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(AccreditationCycle::class, 'instrument_id');
    }
}
