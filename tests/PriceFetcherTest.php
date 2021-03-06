<?php

namespace App\Tests;

use App\PriceFetcher;
use App\Result\Row;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceFetcherTest extends TestCase
{

    private PriceFetcher $priceFetcher;
    private MockObject $client;

    public function testSearchForRow(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('https://store.playstation.com/store/api/chihiro/00_09_000/tumbler/US/en/999/Coffee+Break')
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/sample-response-prices.json')));

        $money = $this->priceFetcher->searchForRow(Row::fromArray([
            'title' => 'Coffee Break',
            'region' => 'EU',
        ]));
        $this->assertEquals(new Money(699, new Currency('EUR')), $money);
    }

    public function testItShouldThrowWhenNoRegexMatches(): void
    {
        $this->client
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['https://store.playstation.com/store/api/chihiro/00_09_000/tumbler/US/en/999/test+with+weird+chars'],
                ['https://store.playstation.com/store/api/chihiro/00_09_000/tumbler/BE/nl/999/test+with+weird+chars']
            )
            ->willReturn(new Response(200, [], 'some-html'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not fetch data for "test-with:weird-chars" in region EU');

        $this->priceFetcher->searchForRow(Row::fromArray([
            'title' => 'test-with:weird-chars',
            'region' => 'EU',
        ]));
    }

    public function testItShouldThrowWhenNoMatchingPrices(): void
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->with('https://store.playstation.com/store/api/chihiro/00_09_000/tumbler/US/en/999/test+with+weird+chars')
            ->willReturn(new Response(200, [], file_get_contents(__DIR__ . '/sample-response-prices.json')));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not determine price for "test-with:weird-chars"');

        $this->priceFetcher->searchForRow(Row::fromArray([
            'title' => 'test-with:weird-chars',
            'region' => 'EU',
        ]));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->priceFetcher = new PriceFetcher(
            $this->client
        );
    }
}
