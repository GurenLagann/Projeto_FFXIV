<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeLookup extends Model
{
    protected $table     = 'recipe_lookup';
    public $incrementing = false;
    protected $keyType   = 'string';
    protected $primaryKey = 'item_id';

    protected $fillable = [
        'item_id',
        'crp_id', 'bsm_id', 'arm_id', 'gsm_id',
        'ltw_id', 'wvr_id', 'alc_id', 'cul_id',
    ];

    protected $casts = [
        'crp_id' => 'integer',
        'bsm_id' => 'integer',
        'arm_id' => 'integer',
        'gsm_id' => 'integer',
        'ltw_id' => 'integer',
        'wvr_id' => 'integer',
        'alc_id' => 'integer',
        'cul_id' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /** Retorna as siglas dos jobs que têm receita para este item. */
    public function getJobAbbreviations(): array
    {
        return array_values(array_filter([
            $this->crp_id ? 'CRP' : null,
            $this->bsm_id ? 'BSM' : null,
            $this->arm_id ? 'ARM' : null,
            $this->gsm_id ? 'GSM' : null,
            $this->ltw_id ? 'LTW' : null,
            $this->wvr_id ? 'WVR' : null,
            $this->alc_id ? 'ALC' : null,
            $this->cul_id ? 'CUL' : null,
        ]));
    }

    /** Retorna o recipe_id para um job específico (ex: 'bsm'). */
    public function getRecipeIdForJob(string $jobAbbr): ?int
    {
        $field = strtolower($jobAbbr) . '_id';
        return $this->{$field} ?? null;
    }
}
