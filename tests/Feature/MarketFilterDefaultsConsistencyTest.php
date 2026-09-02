<?php

namespace Tests\Feature;

use App\Http\Requests\AnalyzeMarketRequest;
use App\Livewire\MarketAnalyzer;
use App\Support\MarketFilterDefaults;
use Illuminate\Http\Request;
use Tests\TestCase;

class MarketFilterDefaultsConsistencyTest extends TestCase
{
    public function test_livewire_dashboard_defaults_match_the_shared_defaults(): void
    {
        $component = new MarketAnalyzer();

        $this->assertSame(MarketFilterDefaults::MIN_LEVEL, $component->minLevel);
        $this->assertSame(MarketFilterDefaults::MAX_LEVEL, $component->maxLevel);
        $this->assertSame(MarketFilterDefaults::MIN_PROFIT, $component->minProfit);
        $this->assertSame(MarketFilterDefaults::MIN_MARGIN, $component->minMargin);
        $this->assertSame(MarketFilterDefaults::MIN_SALES, $component->minSales);
    }

    public function test_web_analyze_request_falls_back_to_the_shared_defaults_when_fields_are_omitted(): void
    {
        $request = AnalyzeMarketRequest::create('/analyze', 'POST', [
            'server_id'      => 1,
            'cost_metric'    => 'min_listing',
            'revenue_metric' => 'home_min_listing',
        ]);

        $filters = $request->toFilters();

        $this->assertSame(MarketFilterDefaults::MIN_PROFIT, $filters['min_profit']);
        $this->assertSame(MarketFilterDefaults::MIN_MARGIN, $filters['min_margin']);
        $this->assertSame(MarketFilterDefaults::MIN_SALES, $filters['min_sales']);
    }
}
