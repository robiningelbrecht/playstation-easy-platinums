<?php

namespace App;

use SleekDB\Store;

class GameRepository
{
    public const SLEEKDB_DIRECTORY = './database';

    public function __construct(
        private readonly Store $store
    )
    {
    }

    public function findAll(): array
    {
        return $this->store->findBy(["removedOn", "==", ""], ['id' => 'DESC']);
    }

    public function find(string $id): array
    {
        if (!$row = $this->store->findById($id)) {
            throw new \RuntimeException(sprintf('Invalid id "%s" provided', $id));
        }
        if (!empty($row['removedOn'])) {
            throw new \RuntimeException(sprintf('Invalid id "%s" provided', $id));
        }

        return $row;
    }

    public function findIncludingRemoved(string $id): array
    {
        if (!$row = $this->store->findById($id)) {
            throw new \RuntimeException(sprintf('Invalid id "%s" provided', $id));
        }

        return $row;
    }

    public function findDistinctValuesForColumn(string $columnName): array
    {
        $rows = $this->store->createQueryBuilder()
            ->distinct($columnName)
            ->getQuery()
            ->fetch();

        $values = array_filter(array_column($rows, $columnName));
        sort($values);

        return $values;
    }

    public function save(array $row): void
    {
        $this->store->updateOrInsert($row, false);
    }

    public function saveMany(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $this->store->updateOrInsertMany($rows, false);
    }
}