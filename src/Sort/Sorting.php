<?php

namespace App\Sort;

class Sorting
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
        return new self(SortField::ID, SortDirection::DESC);
    }

    public function getSortField(): SortField
    {
        return $this->sortField;
    }

    public function getSortDirection(): SortDirection
    {
        return $this->sortDirection;
    }

    public static function renderSort(
        string $fieldName,
        Sorting $currentSorting,
        int $currentPage): string
    {
        $fieldName = SortField::from($fieldName);
        $ascUri = 'https://github.com/robiningelbrecht/playstation-easy-platinums/blob/master/public/PAGE-' . $currentPage . '-SORT_' . $fieldName->toUpper() . '_ASC.md';
        $descUri = 'https://github.com/robiningelbrecht/playstation-easy-platinums/blob/master/public/PAGE-' . $currentPage . '-SORT_' . $fieldName->toUpper() . '_DESC.md';
        if ($fieldName === $currentSorting->getSortField()) {
            if ($currentSorting->getSortDirection() === SortDirection::DESC) {
                return '<a href="' . $ascUri . '">▲</a>▼';
            }
            return '▲<a href="' . $descUri . '">▼</a>';
        }
        return '<a href="' . $ascUri . '">▲</a><a href="' . $descUri . '">▼</a>';
    }
}