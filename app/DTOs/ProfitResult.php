<?php

namespace App\DTOs;

readonly class ProfitResult implements \JsonSerializable
{
    public function __construct(
        public int     $itemId,
        public string  $itemName,
        public ?string $itemIcon,
        public int     $profit,
        public int     $costEstimate,
        public int     $revenueEstimate,
        public int     $yieldsPerCraft,
        public float   $salesPerWeek,
        public float   $marginPercent,
        public bool    $isProfitable,
        public array   $craftJobs          = [],   // ['BSM', 'ARM', ...]
        public bool    $allMatsGatherable  = false,
        public string  $sourceType         = 'craft',     // 'craft' | 'gathering'
        public ?string $gatheringSource    = null,         // 'gathering' (MIN/BTN) | 'fishing' (FSH)
    ) {}

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
