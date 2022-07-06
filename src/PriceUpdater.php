<?php

namespace App;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;

class PriceUpdater
{
    public function __construct(
        private readonly FileContentsWrapper $fileContentsWrapper,
    )
    {
    }

    public function doUpdateForId(string $id, int $amountInCents, string $currency): void
    {
        if (!file_exists(GameFetcher::JSON_FILE)) {
            throw new \RuntimeException('easy-platinums.json not found. Run "fetch" first');
        }

        $json = json_decode($this->fileContentsWrapper->get(GameFetcher::JSON_FILE), true);
        if (!array_key_exists($id, $json)) {
            throw new \RuntimeException('Invalid id provided');
        }

        if (!(new ISOCurrencies())->contains(new Currency($currency))) {
            throw new \RuntimeException('Invalid currency provided');
        }

        $json[$id]['price'] = new Money($amountInCents, new Currency($currency));
        $this->fileContentsWrapper->put(GameFetcher::JSON_FILE, json_encode($json));
    }
}