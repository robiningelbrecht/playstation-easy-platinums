<?php

namespace App\Tests;

use App\FileContentsWrapper;
use App\PriceUpdater;
use App\Result\Row;
use Money\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class PriceUpdaterTest extends TestCase
{
    use MatchesSnapshots;

    private PriceUpdater $priceUpdater;
    private MockObject $fileContentsWrapper;

    public function testDoUpdateForId(): void
    {
        $this->fileContentsWrapper
            ->expects($this->once())
            ->method('get')
            ->with('easy-platinums.json')
            ->willReturn(file_get_contents(__DIR__ . '/easy-platinums.json'));

        $this->fileContentsWrapper
            ->expects($this->once())
            ->method('put')
            ->willReturnCallback(function (string $file, string $content) {
                $this->assertEquals('easy-platinums.json', $file);
                $this->assertMatchesJsonSnapshot(json_encode($content));
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
                'price' => Money::USD(199),
            ]),
            $this->priceUpdater->doUpdateForId('16927', 199)
        );
    }

    public function testItShouldThrowWhenInvalidId(): void
    {
        $this->fileContentsWrapper
            ->expects($this->once())
            ->method('get')
            ->with('easy-platinums.json')
            ->willReturn(file_get_contents(__DIR__ . '/easy-platinums.json'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid id "some-id" provided');

        $this->priceUpdater->doUpdateForId('some-id', 199);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileContentsWrapper = $this->createMock(FileContentsWrapper::class);

        $this->priceUpdater = new PriceUpdater(
            $this->fileContentsWrapper
        );
    }
}
