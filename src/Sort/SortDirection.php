<?php

namespace App\Sort;

enum SortDirection: string
{
    case ASC = 'asc';
    case DESC = 'desc';

    public function toUpper(): string
    {
        return strtoupper($this->value);
    }
}