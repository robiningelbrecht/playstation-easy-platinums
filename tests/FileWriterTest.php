<?php

namespace App\Tests;

use App\Clock\Clock;
use App\Clock\PausedClock;
use App\FileContentsWrapper;
use App\FileWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class FileWriterTest extends TestCase
{
    use MatchesSnapshots;

    private FileWriter $fileWriter;
    private readonly MockObject $fileContentsWrapper;
    private Clock $clock;

    public function testWritePages(): void
    {

        $this->fileContentsWrapper
            ->expects($this->exactly(1))
            ->method('get')
            ->with('easy-platinums.json')
            ->willReturn(
                file_get_contents(__DIR__.'/easy-platinums.json'),
            );

        $this->fileContentsWrapper
            ->expects($this->exactly(14))
            ->method('put')
            ->willReturnCallback(function (string $file, string $content) {
                $this->assertMatchesJsonSnapshot(json_encode($file));
                $this->assertMatchesJsonSnapshot(json_encode($content));
            });

        $this->fileWriter->writePages();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileContentsWrapper = $this->createMock(FileContentsWrapper::class);
        $this->clock = PausedClock::on(new \DateTimeImmutable('2022-07-01'));
        $this->fileWriter = new FileWriter(
            $this->fileContentsWrapper,
            $this->clock
        );
    }
}