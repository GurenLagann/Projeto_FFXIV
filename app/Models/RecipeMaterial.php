<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeMaterial extends Model
{
    public $timestamps = false;

    protected $table = 'recipe_materials';

    protected $fillable = ['recipe_id', 'material_id', 'quantity'];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'material_id');
    }
}
