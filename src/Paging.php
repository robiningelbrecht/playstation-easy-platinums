<?php

namespace App;

use App\Result\ResultSet;

class Paging
{
    public const PAGE_SIZE = 100;

    private function __construct(
        private readonly int $totalPages,
        private readonly int $currentPage,
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

    public static function fromTotalRowCountAndCurrentPage(int $totalRowCount, int $currentPage): self
    {
        return new self(
            static::calculateTotalPages($totalRowCount),
            $currentPage
        );
    }

    public static function calculateTotalPages(int $totalRowCount): int
    {
        return ceil($totalRowCount / self::PAGE_SIZE);
    }
}