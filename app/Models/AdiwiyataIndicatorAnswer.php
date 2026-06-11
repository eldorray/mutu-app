<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdiwiyataIndicatorAnswer extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'checked_evidences' => 'array',
            'value_percentage' => 'decimal:2',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AdiwiyataCycle::class, 'cycle_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AdiwiyataIndicator::class, 'indicator_id');
    }

    public function filledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filled_by');
    }
}
