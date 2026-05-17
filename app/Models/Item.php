<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'level', 'job_id', 'stars', 'icon', 'is_craftable'];

    protected $casts = [
        'level'        => 'integer',
        'stars'        => 'integer',
        'job_id'       => 'integer',
        'is_craftable' => 'boolean',
    ];

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    public function recipeLookup(): HasOne
    {
        return $this->hasOne(RecipeLookup::class, 'item_id');
    }

    public function gatheringItem(): HasOne
    {
        return $this->hasOne(GatheringItem::class, 'item_id');
    }

    public function asIngredientIn(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_materials', 'material_id', 'recipe_id')
            ->withPivot('quantity');
    }
}
