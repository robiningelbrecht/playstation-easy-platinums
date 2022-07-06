<?php

namespace App;

use App\Result\Row;
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

    public function doUpdateForId(string $id, int $amountInCents): void
    {
        if (!file_exists(GameFetcher::JSON_FILE)) {
            throw new \RuntimeException('easy-platinums.json not found. Run "fetch" first');
        }

        $json = json_decode($this->fileContentsWrapper->get(GameFetcher::JSON_FILE), true);
        if (!array_key_exists($id, $json)) {
            throw new \RuntimeException('Invalid id provided');
        }

        $row = Row::fromArray($json[$id]);

        $json[$id]['price'] = new Money($amountInCents, PriceFetcher::getCurrencyForRegion($row->getRegion()));
        $this->fileContentsWrapper->put(GameFetcher::JSON_FILE, json_encode($json));
    }
}