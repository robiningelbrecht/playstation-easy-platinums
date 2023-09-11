<?php

namespace App\Sort;

readonly class Sorting
{
    private function __construct(
        private SortField $sortField,
        private SortDirection $sortDirection,
    )
    {
    }

    public static function fromFieldAndDirection(
        SortField $sortField,
        SortDirection $sortDirection,
    ): self
    {
        return new self($sortField, $sortDirection);
    }

    public static function default(): self
    {
        return new self(SortField::DATE, SortDirection::DESC);
    }

    public function getSortField(): SortField
    {
        return $this->sortField;
    }

    public function getSortDirection(): SortDirection
    {
        return $this->sortDirection;
    }
}