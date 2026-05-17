<?php

namespace App\Enums;

enum CostMetric: string
{
    case MIN_LISTING  = 'min_listing';
    case MEDIAN_SALE  = 'median_sale';
    case AVERAGE_SALE = 'average_sale';
    case RECENT_SALE  = 'recent_sale';

    public function getUniversalisField(): string
    {
        return match($this) {
            self::MIN_LISTING  => 'minPriceNQ',
            self::MEDIAN_SALE  => 'medianSalePriceNQ',
            self::AVERAGE_SALE => 'averageSalePriceNQ',
            self::RECENT_SALE  => 'recentPurchasePriceNQ',
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::MIN_LISTING  => 'Minimum Listing',
            self::MEDIAN_SALE  => 'Median Sale Price',
            self::AVERAGE_SALE => 'Average Sale Price',
            self::RECENT_SALE  => 'Recent Purchase Price',
        };
    }
}
