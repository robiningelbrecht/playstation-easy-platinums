<?php

namespace App;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

class MoneyFormatter
{
    public function __construct(
        private ISOCurrencies $currencies = new ISOCurrencies()
    )
    {
    }

    public function format(Money $money): string
    {
        $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $this->currencies);

        return $moneyFormatter->format($money);
    }
}