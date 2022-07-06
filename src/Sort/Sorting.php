<?php

namespace App\Sort;

class Sorting
{
    private function __construct(
        private readonly SortField $sortField,
        private readonly SortDirection $sortDirection,
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