<?php

namespace App\Sort;

enum SortField: string
{
    case ID = 'id';
    case TITLE = 'title';
    case TIME = 'time';
    case POINTS = 'points';
    case TROPHIES = 'trophies';
    case PRICE = 'price';

    public function toUpper(): string
    {
        return strtoupper($this->value);
    }

    public function getType(): int
    {
        return match ($this) {
            SortField::TITLE => SORT_NATURAL,
            default => SORT_NUMERIC
        };
    }
}