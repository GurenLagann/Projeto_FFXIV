<?php

namespace App\Http\Requests;

use App\Support\MarketFilterDefaults;
use Illuminate\Foundation\Http\FormRequest;

class AnalyzeMarketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'server_id'      => 'required|exists:servers,id',
            'cost_metric'    => 'required|string',
            'revenue_metric' => 'required|string',
            'job_id'         => 'nullable|integer',
            'min_level'      => 'nullable|integer|min:1|max:100',
            'max_level'      => 'nullable|integer|min:1|max:100',
            'min_profit'     => 'nullable|integer|min:0',
            'min_margin'     => 'nullable|numeric|min:0|max:100',
            'min_sales'      => 'nullable|numeric|min:0',
        ];
    }

    public function toFilters(): array
    {
        return array_filter([
            'cost_metric'    => $this->cost_metric,
            'revenue_metric' => $this->revenue_metric,
            'job_id'         => $this->job_id ? (int) $this->job_id : null,
            'min_level'      => $this->min_level ? (int) $this->min_level : null,
            'max_level'      => $this->max_level ? (int) $this->max_level : null,
            'min_profit'     => (int) ($this->min_profit ?? MarketFilterDefaults::MIN_PROFIT),
            'min_margin'     => (float) ($this->min_margin ?? MarketFilterDefaults::MIN_MARGIN),
            'min_sales'      => (float) ($this->min_sales ?? MarketFilterDefaults::MIN_SALES),
        ], fn($v) => $v !== null);
    }
}
