<?php

namespace App\Result;

use App\Sort\SortField;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

class Row
{

    private function __construct(
        private array $data
    )
    {
    }

    public function getId(): int
    {
        return (int)$this->data['id'];
    }

    public function getUniqueValue(): string
    {
        return $this->getTitle() . $this->getPlatform() . ($this->getRegion() ?? '');
    }

    public function getTitle(): string
    {
        return $this->data['title'];
    }

    public function getRegion(): ?string
    {
        return $this->data['region'];
    }

    public function getPlatform(): string
    {
        return $this->data['platform'];
    }

    public function getThumbnail(): string
    {
        return $this->data['thumbnail'];
    }

    public function getUri(): string
    {
        return $this->data['uri'];
    }

    public function getApproximateTime(): int
    {
        return (int)$this->data['approximateTime'];
    }

    public function getTrophiesTotal(): int
    {
        return (int)$this->data['trophiesTotal'];
    }

    public function getTrophiesGold(): int
    {
        return (int)$this->data['trophiesGold'];
    }

    public function getTrophiesSilver(): int
    {
        return (int)$this->data['trophiesSilver'];
    }

    public function getTrophiesBronze(): int
    {
        return (int)$this->data['trophiesBronze'];
    }

    public function getPoints(): int
    {
        return (int)($this->getTrophiesBronze() * 15) + ($this->getTrophiesSilver() * 30) + ($this->getTrophiesGold() * 90) + 300;
    }

    public function getPrice(): ?Money
    {
        if (!$this->data['price']) {
            return null;
        }

        return new Money(
            $this->data['price']['amount'],
            new Currency($this->data['price']['currency'])
        );
    }

    public function getPriceFormattedAsMoney(): ?string
    {
        if (!$money = $this->getPrice()) {
            return null;
        }

        $currencies = new ISOCurrencies();



        $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        return $moneyFormatter->format($money);
    }

    public function getValueForSortField(SortField $sortField): string|int|null
    {
        return match ($sortField) {
            SortField::TROPHIES => $this->getTrophiesTotal(),
            SortField::POINTS => $this->getPoints(),
            SortField::TIME => $this->getApproximateTime(),
            SortField::PRICE => $this->getPrice()?->getAmount(),
            default => $this->data[$sortField->value],
        };
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}