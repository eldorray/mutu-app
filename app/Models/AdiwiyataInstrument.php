<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdiwiyataInstrument extends Model
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
        return $this->hasMany(AdiwiyataComponent::class, 'instrument_id')->orderBy('sort_order');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(AdiwiyataIndicator::class, 'instrument_id')->orderBy('sort_order');
    }
}
