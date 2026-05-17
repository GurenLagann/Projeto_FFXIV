<?php

namespace App\Enums;

enum RevenueMetric: string
{
    case HOME_MIN_LISTING   = 'home_min_listing';
    case REGION_MIN_LISTING = 'region_min_listing';
    case REGION_MEDIAN      = 'region_median';
    case GLOBAL_AVERAGE     = 'global_average';

    public function getUniversalisField(): string
    {
        return match($this) {
            self::HOME_MIN_LISTING   => 'minPriceNQ',
            self::REGION_MIN_LISTING => 'regionMinPriceNQ',
            self::REGION_MEDIAN      => 'regionMedianSalePriceNQ',
            self::GLOBAL_AVERAGE     => 'averageSalePriceNQ',
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::HOME_MIN_LISTING   => 'Home Server Min Listing',
            self::REGION_MIN_LISTING => 'Region Min Listing',
            self::REGION_MEDIAN      => 'Region Median Sale',
            self::GLOBAL_AVERAGE     => 'Global Average Sale',
        };
    }
}
