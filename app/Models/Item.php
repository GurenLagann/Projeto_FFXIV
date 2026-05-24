<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'level', 'job_id', 'stars', 'icon', 'is_craftable'];

    protected $appends = ['icon_url'];

    protected $casts = [
        'level'        => 'integer',
        'stars'        => 'integer',
        'job_id'       => 'integer',
        'is_craftable' => 'boolean',
    ];

    /**
     * Resolve o ícone para URL absoluta.
     *
     * Dois formatos existem no banco dependendo de qual API os gravou:
     *   - v1 XIVAPI (recipes):   "/i/022000/022001.png"       → xivapi.com
     *   - v2 XIVAPI (gathering): "/ui/icon/022000/022001.png" → v2.xivapi.com
     */
    public function getIconUrlAttribute(): ?string
    {
        if (!$this->icon) {
            return null;
        }

        if (str_starts_with($this->icon, 'http')) {
            return $this->icon;
        }

        if (str_starts_with($this->icon, '/ui/')) {
            return 'https://v2.xivapi.com' . $this->icon;
        }

        return 'https://xivapi.com' . $this->icon;
    }

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
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
