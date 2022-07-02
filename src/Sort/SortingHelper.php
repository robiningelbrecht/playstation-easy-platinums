<?php

namespace App\Sort;

class SortingHelper
{
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