<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $guarded = [];

    public function cycles(): HasMany
    {
        return $this->hasMany(AccreditationCycle::class, 'school_id');
    }

    public function adiwiyataCycles(): HasMany
    {
        return $this->hasMany(AdiwiyataCycle::class, 'school_id');
    }
}
