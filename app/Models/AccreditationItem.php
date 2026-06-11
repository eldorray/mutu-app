<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationItem extends Model
{
    protected $guarded = [];

    public function component(): BelongsTo
    {
        return $this->belongsTo(AccreditationComponent::class, 'component_id');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(AccreditationIndicator::class, 'item_id')->orderBy('sort_order');
    }
}
