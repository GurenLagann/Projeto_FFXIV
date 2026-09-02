<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analysis extends Model
{
    protected $fillable = [
        'server_id',
        'filters',
        'results',
        'execution_time',
        'total_opportunities',
        'completed_at',
        'failed_at',
        'error',
    ];

    protected $casts = [
        'filters'            => 'array',
        'results'            => 'array',
        'execution_time'     => 'integer',
        'total_opportunities'=> 'integer',
        'completed_at'       => 'datetime',
        'failed_at'          => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
