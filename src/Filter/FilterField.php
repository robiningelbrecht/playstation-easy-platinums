<?php

namespace App\Filter;

enum FilterField: string
{
    case REGION = 'region';
    case PLATFORM = 'platform';

    public function toUpper(): string
    {
        return strtoupper($this->value);
    }
}