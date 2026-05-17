<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatheringItem extends Model
{
    public $incrementing = false;
    protected $keyType    = 'string';
    protected $primaryKey = 'item_id';

    protected $fillable = ['item_id', 'gathering_level', 'stars', 'source'];

    protected $casts = [
        'gathering_level' => 'integer',
        'stars'           => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function getSourceLabelAttribute(): string
    {
        return match($this->source) {
            'fishing'   => 'FSH',
            'gathering' => 'MIN/BTN',
            default     => '?',
        };
    }

    public function getLevelLabelAttribute(): string
    {
        return 'Lv.' . $this->gathering_level
            . ($this->stars > 0 ? ' ' . str_repeat('★', $this->stars) : '');
    }
}
