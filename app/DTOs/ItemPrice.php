<?php

namespace App\DTOs;

readonly class ItemPrice
{
    public function __construct(
        public int   $itemId,
        public int   $minPriceNQ,
        public int   $minPriceHQ,
        public float $medianSalePriceNQ,
        public float $medianSalePriceHQ,
        public float $averageSalePriceNQ,
        public int   $recentPurchasePriceNQ,
        public int   $regionMinPriceNQ,
        public float $regionMedianSalePriceNQ,
        public float  $salesPerWeek,
        public ?int   $lastUploadTime = null,
    ) {}

    public static function fromUniversalisResponse(array $data, int $itemId): self
    {
        $listings = $data['listings'] ?? [];
        $nqListings = array_filter($listings, fn($l) => !($l['hq'] ?? false));
        $hqListings = array_filter($listings, fn($l) => $l['hq'] ?? false);

        $minNQ = empty($nqListings) ? 0 : min(array_column(array_values($nqListings), 'pricePerUnit'));
        $minHQ = empty($hqListings) ? 0 : min(array_column(array_values($hqListings), 'pricePerUnit'));

        $nqPrices = array_column(array_values($nqListings), 'pricePerUnit');
        sort($nqPrices);
        $medianNQ = empty($nqPrices) ? 0.0 : (float) $nqPrices[(int)(count($nqPrices) / 2)];

        $hqPrices = array_column(array_values($hqListings), 'pricePerUnit');
        sort($hqPrices);
        $medianHQ = empty($hqPrices) ? 0.0 : (float) $hqPrices[(int)(count($hqPrices) / 2)];

        $recentHistory = $data['recentHistory'] ?? [];
        $recentNQ = array_filter($recentHistory, fn($h) => !($h['hq'] ?? false));
        $recentPriceNQ = empty($recentNQ) ? 0 : (int) array_values($recentNQ)[0]['pricePerUnit'];

        return new self(
            itemId:                  $itemId,
            minPriceNQ:              $minNQ,
            minPriceHQ:              $minHQ,
            medianSalePriceNQ:       $medianNQ,
            medianSalePriceHQ:       $medianHQ,
            averageSalePriceNQ:      (float) ($data['averagePrice'] ?? 0),
            recentPurchasePriceNQ:   $recentPriceNQ,
            regionMinPriceNQ:        (int) ($data['minPriceNQ'] ?? $minNQ),
            regionMedianSalePriceNQ: (float) ($data['medianSalePrice'] ?? $medianNQ),
            salesPerWeek:            (float) ($data['regularSaleVelocity'] ?? 0),
            lastUploadTime:          isset($data['lastUploadTime']) ? (int) ($data['lastUploadTime'] / 1000) : null,
        );
    }

    public function getFieldValue(string $field): int|float
    {
        return match($field) {
            'minPriceNQ'              => $this->minPriceNQ,
            'medianSalePriceNQ'       => $this->medianSalePriceNQ,
            'averageSalePriceNQ'      => $this->averageSalePriceNQ,
            'recentPurchasePriceNQ'   => $this->recentPurchasePriceNQ,
            'regionMinPriceNQ'        => $this->regionMinPriceNQ,
            'regionMedianSalePriceNQ' => $this->regionMedianSalePriceNQ,
            default                   => $this->minPriceNQ,
        };
    }
}
