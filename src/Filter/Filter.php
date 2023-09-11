<?php

namespace App\Filter;

readonly class Filter
{
    private function __construct(
        private FilterField $filterField,
        private string $value,
    )
    {
    }

    public function getFilterField(): FilterField
    {
        return $this->filterField;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function fromFilterFieldAndValue(FilterField $filterField, string $value): self
    {

        return new self($filterField, $value);
    }
}