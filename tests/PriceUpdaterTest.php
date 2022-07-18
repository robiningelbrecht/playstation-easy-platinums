<?php

namespace App\Tests;

use App\FileContentsWrapper;
use App\GameRepository;
use App\PriceUpdater;
use App\Result\Row;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class PriceUpdaterTest extends TestCase
{
    use MatchesSnapshots;

    private PriceUpdater $priceUpdater;
    private readonly MockObject $gameRepository;

    public function testDoUpdateForId(): void
    {
        $this->gameRepository
            ->expects($this->once())
            ->method('find')
            ->with(16927)
            ->willReturn(
                [
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
                    'price' => null,
                ]
            );

        $this->gameRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (array $row) {
                $this->assertMatchesJsonSnapshot(json_encode($row));
            });

        $this->assertEquals(
            Row::fromArray([
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
                'price' => ['amount' => 199, 'currency' => 'USD'],
            ]),
            $this->priceUpdater->doUpdateForId('16927', 199)
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->gameRepository = $this->createMock(GameRepository::class);

        $this->priceUpdater = new PriceUpdater(
            $this->gameRepository
        );
    }
}
