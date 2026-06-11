<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationEvidenceLink extends Model
{
    protected $guarded = [];

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(AccreditationEvidence::class, 'evidence_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AccreditationIndicator::class, 'indicator_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AccreditationItem::class, 'item_id');
    }
}
