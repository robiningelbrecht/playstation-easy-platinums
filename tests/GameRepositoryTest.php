<?php

namespace App\Tests;

use App\FileContentsWrapper;
use App\GameRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class GameRepositoryTest extends TestCase
{
    use MatchesSnapshots;

    private GameRepository $gameRepository;
    private readonly MockObject $fileContentsWrapper;

    public function testFindAll(): void
    {
        $content = file_get_contents(__DIR__ . '/easy-platinums.json');
        $this->fileContentsWrapper
            ->expects($this->exactly(1))
            ->method('get')
            ->with('easy-platinums.json')
            ->willReturn($content);

        $this->assertEquals(json_decode($content, true), $this->gameRepository->findAll());
    }

    public function testFind(): void
    {
        $content = file_get_contents(__DIR__ . '/easy-platinums.json');
        $this->fileContentsWrapper
            ->expects($this->exactly(1))
            ->method('get')
            ->with('easy-platinums.json')
            ->willReturn($content);

        $this->assertMatchesJsonSnapshot($this->gameRepository->find(16917));
    }

    public function testFindItShouldThrowWhenNotFound(): void{
        $this->fileContentsWrapper
            ->expects($this->exactly(1))
            ->method('get')
            ->with('easy-platinums.json')
            ->willReturn('[]');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid id "16917" provided');

        $this->assertMatchesJsonSnapshot($this->gameRepository->find(16917));
    }

    public function testSave(): void
    {
        $this->fileContentsWrapper
            ->expects($this->exactly(1))
            ->method('get')
            ->with('easy-platinums.json')
            ->willReturn(json_encode(['2' => ['id' => 2]]));

        $this->fileContentsWrapper
            ->expects($this->exactly(1))
            ->method('put')
            ->with('easy-platinums.json', json_encode(['2' => ['id' => 2], '3' => ['id' => 3]]));

        $this->gameRepository->save(['id' => 3]);
    }

    public function testSaveMany(): void
    {
        $this->fileContentsWrapper
            ->expects($this->exactly(1))
            ->method('get')
            ->with('easy-platinums.json')
            ->willReturn(json_encode(['2' => ['id' => 2]]));

        $this->fileContentsWrapper
            ->expects($this->exactly(1))
            ->method('put')
            ->with('easy-platinums.json', json_encode(['2' => ['id' => 2], '3' => ['id' => 3], '4' => ['id' => 4]]));

        $this->gameRepository->saveMany([['id' => 3], ['id' => 4]]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileContentsWrapper = $this->createMock(FileContentsWrapper::class);

        $this->gameRepository = new GameRepository($this->fileContentsWrapper);
    }
}
