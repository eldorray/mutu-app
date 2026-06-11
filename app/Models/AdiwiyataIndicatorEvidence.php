<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdiwiyataIndicatorEvidence extends Model
{
    protected $guarded = [];

    protected $table = 'adiwiyata_indicator_evidences';

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AdiwiyataIndicator::class, 'indicator_id');
    }
}
