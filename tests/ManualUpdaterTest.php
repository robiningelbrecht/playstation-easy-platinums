<?php

namespace App\Tests;

use App\Clock\Clock;
use App\Clock\PausedClock;
use App\FileContentsWrapper;
use App\GameRepository;
use App\ManualUpdater;
use App\Result\Row;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ManualUpdaterTest extends TestCase
{
    use MatchesSnapshots;

    private ManualUpdater $manualUpdater;
    private readonly MockObject $gameRepository;
    private Clock $clock;

    public function testUpdatePriceForId(): void
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
            $this->manualUpdater->updatePriceForId('16927', 199)
        );
    }

    public function testRemoveGameById(): void
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
                'removedOn' => $this->clock->getCurrentDateTimeImmutable()->format(Clock::DEFAULT_FORMAT),
                'price' => null,
            ]),
            $this->manualUpdater->removeGameById('16927')
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->gameRepository = $this->createMock(GameRepository::class);
        $this->clock = PausedClock::on(new \DateTimeImmutable('2022-07-18'));

        $this->manualUpdater = new ManualUpdater(
            $this->gameRepository,
            $this->clock
        );
    }
}
