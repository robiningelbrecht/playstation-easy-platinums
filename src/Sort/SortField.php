<?php

namespace App\Sort;

enum SortField: string
{

    case DATE = 'date';
    case TITLE = 'title';
    case TIME = 'time';
    case POINTS = 'points';
    case TROPHIES = 'trophies';
    case PRICE = 'price';

    public function toUpper(): string
    {
        return strtoupper($this->value);
    }
}