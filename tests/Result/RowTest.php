<?php

namespace Result;

use App\Result\Row;
use Money\Money;
use PHPUnit\Framework\TestCase;

class RowTest extends TestCase
{

    public function testGetFullTitle(): void
    {
        $row = Row::fromArray([
            'id' => '16927',
            'title' => 'Rainbow Advanced',
            'region' => null,
            'platform' => 'PS4',
            'thumbnail' => '16927.png',
            'uri' => 'https://psnprofiles.com/trophies/16927-rainbow-advanced',
            'approximateTime' => 1,
            'trophiesTotal' => 19,
            'trophiesGold' => 9,
            'trophiesSilver' => 6,
            'trophiesBronze' => 3,
            'addedOn' => '2022-07-05 07:48:54',
            'price' => Money::USD(199),
        ]);

        $this->assertEquals('Rainbow Advanced (PS4)', $row->getFullTitle());

        $row = Row::fromArray([
            'id' => '16927',
            'title' => 'Rainbow Advanced',
            'region' => 'EU',
            'platform' => 'PS4',
            'thumbnail' => '16927.png',
            'uri' => 'https://psnprofiles.com/trophies/16927-rainbow-advanced',
            'approximateTime' => 1,
            'trophiesTotal' => 19,
            'trophiesGold' => 9,
            'trophiesSilver' => 6,
            'trophiesBronze' => 3,
            'addedOn' => '2022-07-05 07:48:54',
            'price' => Money::USD(199),
        ]);

        $this->assertEquals('Rainbow Advanced (EU • PS4)', $row->getFullTitle());
    }
}
