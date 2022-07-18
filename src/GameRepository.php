<?php

namespace App;

class GameRepository
{
    public const JSON_FILE = 'easy-platinums.json';

    public function __construct(
        private readonly FileContentsWrapper $fileContentsWrapper
    )
    {
    }

    public function findAll(): array
    {
        return json_decode($this->fileContentsWrapper->get(self::JSON_FILE), true);
    }

    public function find(string $id): array{
        $json = $this->findAll();

        if (!array_key_exists($id, $json)) {
            throw new \RuntimeException(sprintf('Invalid id "%s" provided', $id));
        }

        return $json[$id];
    }

    public function save(array $row): void
    {
        $this->saveMany([$row]);
    }

    public function saveMany(array $rows): void
    {
        $json = $this->findAll();
        foreach ($rows as $row) {
            $json[$row['id']] = $row;
        }

        $this->fileContentsWrapper->put(self::JSON_FILE, json_encode($json));
    }
}