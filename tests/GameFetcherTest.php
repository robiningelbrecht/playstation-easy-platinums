<?php

namespace App\Tests;

use App\Clock\PausedClock;
use App\FileContentsWrapper;
use App\GameRepository;
use App\PriceFetcher;
use App\GameFetcher;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class GameFetcherTest extends TestCase
{
    use MatchesSnapshots;

    private GameFetcher $trophyFetcher;
    private readonly MockObject $client;
    private readonly MockObject $fileContentsWrapper;
    private readonly MockObject $gameRepository;
    private readonly MockObject $priceFetcher;

    public function testDoFetch(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('https://psnprofiles.com/Fluttezuhher?ajax=1&page=0')
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/sample-response.json')));

        $this->gameRepository
            ->expects($this->exactly(8))
            ->method('findIncludingRemoved')
            ->willReturn([]);

        $this->fileContentsWrapper
            ->expects($this->exactly(8))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                ...array_map(fn(int $i) => (string)$i, range(1, 8))
            );

        $this->priceFetcher
            ->expects($this->exactly(8))
            ->method('searchForRow')
            ->willReturn(new Money(100, new Currency('EUR')));

        $this->fileContentsWrapper
            ->expects($this->exactly(8))
            ->method('put')
            ->willReturnCallback(function (string $file, string $content) {
                $this->assertMatchesJsonSnapshot(json_encode($file));
                $this->assertMatchesJsonSnapshot(json_encode($content));
            });

        $this->gameRepository
            ->expects($this->once())
            ->method('saveMany')
            ->willReturnCallback(function(array $addedRows){
               $this->assertMatchesJsonSnapshot(json_encode($addedRows));
            });

        $this->trophyFetcher->doFetch('Fluttezuhher');
    }

    public function testItShouldThrowWhenInvalidResponseCode(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('https://psnprofiles.com/Fluttezuhher?ajax=1&page=0')
            ->willReturn(new Response(404));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not fetch games for profile Fluttezuhher');

        $this->trophyFetcher->doFetch('Fluttezuhher');
    }

    public function testItShouldThrowWhenInvalidResponseStructure(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('https://psnprofiles.com/Fluttezuhher?ajax=1&page=0')
            ->willReturn(new Response(200, [], json_encode('[]')));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not fetch games for profile Fluttezuhher');

        $this->trophyFetcher->doFetch('Fluttezuhher');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->fileContentsWrapper = $this->createMock(FileContentsWrapper::class);
        $this->gameRepository = $this->createMock(GameRepository::class);
        $this->priceFetcher = $this->createMock(PriceFetcher::class);

        $this->trophyFetcher = new GameFetcher(
            $this->client,
            $this->fileContentsWrapper,
            $this->gameRepository,
            $this->priceFetcher,
            PausedClock::on(new \DateTimeImmutable('2022-07-01 20:10:04')),
        );
    }
}