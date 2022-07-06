<?php

namespace App\Tests;

use App\FileContentsWrapper;
use App\PriceUpdater;
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
                $this->assertMatchesJsonSnapshot(json_encode($file));
                $this->assertMatchesJsonSnapshot(json_encode($content));
            });

        $this->priceUpdater->doUpdateForId('16927', 199);
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
