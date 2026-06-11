<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdiwiyataEvidence extends Model
{
    protected $guarded = [];

    protected $table = 'adiwiyata_evidences';

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AdiwiyataCycle::class, 'cycle_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AdiwiyataIndicator::class, 'indicator_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
