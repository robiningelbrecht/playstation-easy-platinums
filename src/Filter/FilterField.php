<?php

namespace App\Filter;

class FilterField
{
    public const FIELD_REGION = 'region';
    public const FIELD_PLATFORM = 'platform';
    public const ALL_VALUE = 'All';

   private function __construct(
       private string $name,
       private array $possibleValues,
       private string $defaultValue = self::ALL_VALUE
   )
   {
   }

   public static function fromNameAndPossibleValues(
       string $name,
       array $possibleValues,
       string $defaultValue = self::ALL_VALUE): self
   {
       return new self($name, [self::ALL_VALUE, ...$possibleValues], $defaultValue);
   }

    public function getName(): string
    {
        return $this->name;
    }

    public function toUpper(): string
    {
        return strtoupper($this->name);
    }

    public function getPossibleValues(): array
    {
        return $this->possibleValues;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }
}