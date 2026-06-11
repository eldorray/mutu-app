<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationRubricLevel extends Model
{
    protected $guarded = [];

    public function rubrics(): HasMany
    {
        return $this->hasMany(AccreditationRubric::class, 'rubric_level_id');
    }
}
