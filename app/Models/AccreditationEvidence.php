<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationEvidence extends Model
{
    protected $table = 'accreditation_evidences';

    protected $guarded = [];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AccreditationCycle::class, 'cycle_id');
    }

    public function evidenceType(): BelongsTo
    {
        return $this->belongsTo(AccreditationEvidenceType::class, 'evidence_type_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function links(): HasMany
    {
        return $this->hasMany(AccreditationEvidenceLink::class, 'evidence_id');
    }
}
