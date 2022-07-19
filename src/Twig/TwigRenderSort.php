<?php

namespace App\Twig;

use App\Filter\Filter;
use App\Sort\SortDirection;
use App\Sort\SortField;
use App\Sort\Sorting;

class TwigRenderSort
{
    public static function execute(
        string $fieldName,
        Sorting $currentSorting,
        Filter $filter = null): string
    {
        $fieldName = SortField::from($fieldName);

        $urlParts = [];
        if ($filter) {
            $urlParts[] = 'FILTER_' . $filter->getFilterField()->toUpper() . '_' . $filter->getValue();
        }
        $urlParts[] = 'SORT_' . $fieldName->toUpper();


        $ascUri = 'https://github.com/robiningelbrecht/playstation-easy-platinums/blob/master/public/PAGE-1-' . implode('-', $urlParts) . '_ASC.md';
        $descUri = 'https://github.com/robiningelbrecht/playstation-easy-platinums/blob/master/public/PAGE-1-' . implode('-', $urlParts) . '_DESC.md';

        if ($fieldName === $currentSorting->getSortField()) {
            if ($currentSorting->getSortDirection() === SortDirection::DESC) {
                return '<a href="' . $ascUri . '">▲</a>▼';
            }
            return '▲<a href="' . $descUri . '">▼</a>';
        }
        return '<a href="' . $ascUri . '">▲</a><a href="' . $descUri . '">▼</a>';
    }
}