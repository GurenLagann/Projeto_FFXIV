<?php

namespace App\Support;

/**
 * Single source of truth for the default analysis filters. Used by the web
 * form, the Livewire dashboard and the `market:analyze` CLI command so their
 * defaults can't silently drift apart from one another.
 */
class MarketFilterDefaults
{
    public const MIN_LEVEL  = 1;
    public const MAX_LEVEL  = 100;
    public const MIN_PROFIT = 5000;
    public const MIN_MARGIN = 20.0;
    public const MIN_SALES  = 10.0;
}
