<?php

namespace App\Sort;

use App\Paging;

class SortingHelper
{
    public static function renderSort(
        string $fieldName,
        Sorting $currentSorting,
        Paging $paging): string
    {
        $fieldName = SortField::from($fieldName);
        $currentPage = $paging->getCurrentPage();

        $ascUri = 'https://github.com/robiningelbrecht/playstation-easy-platinums/blob/master/public/PAGE-1-SORT_' . $fieldName->toUpper() . '_ASC.md';
        $descUri = 'https://github.com/robiningelbrecht/playstation-easy-platinums/blob/master/public/PAGE-1-SORT_' . $fieldName->toUpper() . '_DESC.md';

        if ($fieldName === $currentSorting->getSortField()) {
            if ($currentSorting->getSortDirection() === SortDirection::DESC) {
                return '<a href="' . $ascUri . '">▲</a>▼';
            }
            return '▲<a href="' . $descUri . '">▼</a>';
        }
        return '<a href="' . $ascUri . '">▲</a><a href="' . $descUri . '">▼</a>';
    }
}