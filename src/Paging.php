<?php

namespace App;

use App\Result\ResultSet;

class Paging
{
    public const PAGE_SIZE = 250;

    private function __construct(
        private int $totalPages,
        private int $currentPage,
    )
    {
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public static function fromResultSetAndCurrentPage(ResultSet $resultSet, int $currentPage): self
    {
        return new self(
            static::calculateTotalPages($resultSet),
            $currentPage
        );
    }

    public static function calculateTotalPages(ResultSet $resultSet): int
    {
        return ceil(count($resultSet) / self::PAGE_SIZE);
    }
}