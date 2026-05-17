<?php

namespace App\Models;

use App\Enums\Job;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Recipe extends Model
{
    protected $fillable = ['item_id', 'job_id', 'craft_level', 'yields', 'can_be_hq', 'stars', 'difficulty'];

    protected $casts = [
        'craft_level' => 'integer',
        'yields'      => 'integer',
        'stars'       => 'integer',
        'can_be_hq'   => 'boolean',
        'difficulty'  => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'recipe_materials', 'recipe_id', 'material_id')
            ->withPivot('quantity');
    }

    public function getJobAttribute(): ?Job
    {
        return Job::tryFrom($this->job_id);
    }

    public function getJobLabelAttribute(): string
    {
        return $this->job?->getLabel() ?? 'Unknown';
    }
}
