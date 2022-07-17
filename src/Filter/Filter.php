<?php

namespace App\Filter;

class Filter
{
    private function __construct(
        private string $name,
        private array $possibleValues,
        private string $defaultValue,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPossibleValues(): array
    {
        return $this->possibleValues;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public static function nameAndPossibleValues(string $name, array $possibleValues, string $defaultValue = 'All'): self
    {
        return new self($name, $possibleValues, $defaultValue);
    }
}