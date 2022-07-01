<?php

namespace App\Tests;

use App\FileContentsWrapper;
use App\TrophyFetcher;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class TrophyFetcherTest extends TestCase
{
    use MatchesSnapshots;

    private TrophyFetcher $trophyFetcher;
    private readonly MockObject $client;
    private readonly MockObject $fileContentsWrapper;
    private readonly string $psnProfile;

    public function testDoFetch(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('https://psnprofiles.com/Fluttezuhher?ajax=1&page=0')
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/sample-response.json')));

        $this->fileContentsWrapper
            ->expects($this->exactly(86))
            ->method('get')
            ->withConsecutive(
                ['easy-platinums.json']
            )
            ->willReturnOnConsecutiveCalls(
                '[]',
                ...array_map(fn(int $i) => (string)$i, range(1, 100))
            );

        $this->fileContentsWrapper
            ->expects($this->exactly(86))
            ->method('put')
            ->willReturnCallback(function (string $file, string $content) {
                $this->assertMatchesJsonSnapshot(json_encode($file));
                $this->assertMatchesJsonSnapshot(json_encode($content));
            });

        $this->trophyFetcher->doFetch();
    }

    public function testItShouldThrowWhenInvalidResponseCode(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('https://psnprofiles.com/Fluttezuhher?ajax=1&page=0')
            ->willReturn(new Response(404));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not fetch games');

        $this->trophyFetcher->doFetch();
    }

    public function testItShouldThrowWhenInvalidResponseStructure(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('https://psnprofiles.com/Fluttezuhher?ajax=1&page=0')
            ->willReturn(new Response(200, [], json_encode('[]')));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not fetch games');

        $this->trophyFetcher->doFetch();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->fileContentsWrapper = $this->createMock(FileContentsWrapper::class);
        $this->psnProfile = 'Fluttezuhher';

        $this->trophyFetcher = new TrophyFetcher(
            $this->client,
            $this->fileContentsWrapper,
            $this->psnProfile
        );
    }
}