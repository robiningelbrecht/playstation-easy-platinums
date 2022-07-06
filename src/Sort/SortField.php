<?php

namespace App\Sort;

enum SortField: string
{
    public const TYPE_NATURAL = 'natural';
    public const TYPE_NUMERIC = 'numeric';
    public const TYPE_DATE = 'date';

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

    public function getType(): string
    {
        return match ($this) {
            SortField::TITLE => self::TYPE_NATURAL,
            SortField::DATE => self::TYPE_DATE,
            default => self::TYPE_NUMERIC,
        };
    }
}