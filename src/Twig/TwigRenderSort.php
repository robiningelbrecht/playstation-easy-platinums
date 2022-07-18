<?php

namespace App\Twig;

use App\Sort\SortDirection;
use App\Sort\SortField;
use App\Sort\Sorting;

class TwigRenderSort
{
    public static function execute(
        string $fieldName,
        Sorting $currentSorting): string
    {
        $fieldName = SortField::from($fieldName);

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