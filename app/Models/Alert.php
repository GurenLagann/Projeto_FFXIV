<?php

namespace App\Models;

use App\Enums\CostMetric;
use App\Enums\RevenueMetric;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'server_id',
        'min_profit',
        'min_margin',
        'is_active',
        'last_notified_at',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'last_notified_at' => 'datetime',
        'min_profit'       => 'integer',
        'min_margin'       => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
