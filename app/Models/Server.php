<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    protected $fillable = ['name', 'slug', 'datacenter', 'region', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
