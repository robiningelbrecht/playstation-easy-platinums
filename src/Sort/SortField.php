<?php

namespace App\Sort;

enum SortField: string
{
    case ID = 'id';
    case TITLE = 'title';
    case TIME = 'time';
    case POINTS = 'points';
    case TROPHIES = 'trophies';

    public function toUpper(): string
    {
        return strtoupper($this->value);
    }
}