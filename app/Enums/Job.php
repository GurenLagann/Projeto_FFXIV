<?php

namespace App\Enums;

enum Job: int
{
    case OMNICRAFTER   = 0;
    case CARPENTER     = 8;
    case BLACKSMITH    = 9;
    case ARMORER       = 10;
    case GOLDSMITH     = 11;
    case LEATHERWORKER = 12;
    case WEAVER        = 13;
    case ALCHEMIST     = 14;
    case CULINARIAN    = 15;

    public function getLabel(): string
    {
        return match($this) {
            self::OMNICRAFTER   => 'All Crafters',
            self::CARPENTER     => 'Carpenter (CRP)',
            self::BLACKSMITH    => 'Blacksmith (BSM)',
            self::ARMORER       => 'Armorer (ARM)',
            self::GOLDSMITH     => 'Goldsmith (GSM)',
            self::LEATHERWORKER => 'Leatherworker (LTW)',
            self::WEAVER        => 'Weaver (WVR)',
            self::ALCHEMIST     => 'Alchemist (ALC)',
            self::CULINARIAN    => 'Culinarian (CUL)',
        };
    }

    public function getAbbreviation(): string
    {
        return match($this) {
            self::OMNICRAFTER   => 'ALL',
            self::CARPENTER     => 'CRP',
            self::BLACKSMITH    => 'BSM',
            self::ARMORER       => 'ARM',
            self::GOLDSMITH     => 'GSM',
            self::LEATHERWORKER => 'LTW',
            self::WEAVER        => 'WVR',
            self::ALCHEMIST     => 'ALC',
            self::CULINARIAN    => 'CUL',
        };
    }

    public function getIconUrl(): ?string
    {
        return match($this) {
            self::OMNICRAFTER   => null,
            self::CARPENTER     => 'https://xivapi.com/cj/1/carpenter.png',
            self::BLACKSMITH    => 'https://xivapi.com/cj/1/blacksmith.png',
            self::ARMORER       => 'https://xivapi.com/cj/1/armorer.png',
            self::GOLDSMITH     => 'https://xivapi.com/cj/1/goldsmith.png',
            self::LEATHERWORKER => 'https://xivapi.com/cj/1/leatherworker.png',
            self::WEAVER        => 'https://xivapi.com/cj/1/weaver.png',
            self::ALCHEMIST     => 'https://xivapi.com/cj/1/alchemist.png',
            self::CULINARIAN    => 'https://xivapi.com/cj/1/culinarian.png',
        };
    }
}
