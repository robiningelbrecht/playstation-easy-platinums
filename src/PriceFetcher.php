<?php

namespace App;

use App\Result\Row;
use GuzzleHttp\Client;
use Money\Currency;
use Money\Money;

class PriceFetcher
{
    public function __construct(
        private readonly Client $client)
    {
    }

    public function searchForRow(Row $row): Money
    {
        $currency = match ($row->getRegion()) {
            'EU' => new Currency('EUR'),
            default => new Currency('USD')
        };

        $response = $this->client->get('https://store.playstation.com/store/api/chihiro/00_09_000/tumbler/US/en/999/' . urlencode($this->sanitizeSearchQuery($row->getTitle())));
        $json = json_decode($response->getBody()->getContents(), true);

        if (empty($json['links'])) {
            throw new \RuntimeException(sprintf('Could not fetch data for "%s" in region %s', $row->getTitle(), $row->getRegion()));
        }

        foreach ($json['links'] as $result) {
            if (empty($result['name'])) {
                continue;
            }

            if ($this->sanitizeName($result['name']) !== $this->sanitizeName($row->getTitle()) &&
                $this->sanitizeName($result['name']) !== $this->sanitizeName($row->getTitle() . ' PS4 And PS5') &&
                $this->sanitizeName($result['name']) !== $this->sanitizeName($row->getTitle() . ' PS4 & PS5')) {
                continue;
            }

            if (empty($result['default_sku']['price'])) {
                continue;
            }

            return new Money((int)$result['default_sku']['price'], $currency);
        }

        throw new \RuntimeException(sprintf('Could not determine price for "%s"', $row->getTitle()));
    }

    private function sanitizeName(string $string): string
    {
        $string = strtolower(str_replace([':', '-', '—', '(', ')', '[', ']'], '', $string));
        $string = str_replace('&', 'and', $string);
        // Replace multiple spaces with one.
        return preg_replace('/[\s]{2,}/im', ' ', $string);
    }

    private function sanitizeSearchQuery(string $query): string{
        $searchQuery = str_replace([':', '-'], ' ', $query);
        return str_replace(['\'', '"', '’', '`'], '', $searchQuery);
    }


}