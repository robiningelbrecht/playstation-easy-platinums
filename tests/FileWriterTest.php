<?php

namespace App\Tests;

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

    public function testWritePages(): void
    {

        $this->fileContentsWrapper
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['easy-platinums.json'],
                ['README.md']
            )
            ->willReturnOnConsecutiveCalls(
                file_get_contents(__DIR__.'/easy-platinums.json'),
                '<!-- start easy-platinums -->test<!-- end easy-platinums -->'
            );

        $this->fileContentsWrapper
            ->expects($this->exactly(10))
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
        $this->fileWriter = new FileWriter(
            $this->fileContentsWrapper
        );
    }
}