<?php

namespace App\Statistics;

use App\MoneyFormatter;
use App\Result\ResultSet;
use App\Result\Row;
use Money\Money;

readonly class Totals
{
    private function __construct(
        private ResultSet $resultSet
    )
    {
    }

    public static function fromResultSet(ResultSet $resultSet): self
    {
        return new self($resultSet);
    }

    public function getTotalHoursOfGameplay(): int
    {
        $totalMinutes = array_sum(array_map(
            fn(Row $row) => $row->getApproximateTime(),
            $this->resultSet->getRows()
        ));

        return ceil($totalMinutes / 60);
    }

    public function getTotalDaysOfGameplay(): int
    {
        return ceil($this->getTotalHoursOfGameplay() / 24);
    }

    public function getTotalCostPerCurrency(): array
    {
        $moneyFormatter = new MoneyFormatter();
        $totalCostPerCurrency = [];
        $currencies = array_unique(array_filter(array_map(
            fn(Row $row) => $row->getPrice()?->getCurrency(),
            $this->resultSet->getRows()
        )));

        foreach ($currencies as $currency) {
            $costForCurrency = array_filter(
                array_map(
                    fn(Row $row) => $row->getPrice(),
                    array_filter($this->resultSet->getRows(), fn(Row $row) => !empty($row->getPrice()))
                ),
                fn(Money $price) => $price->getCurrency() == $currency
            );

            $totalCostPerCurrency[(string)$currency] = $moneyFormatter->format(Money::sum(...$costForCurrency));
        }

        return $totalCostPerCurrency;
    }
}