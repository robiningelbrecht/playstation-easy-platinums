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

        $searchQuery = str_replace([':', '-'], ' ', $row->getTitle());
        $response = $this->client->get('https://psprices.com/region-us/search/?q=' . urlencode($searchQuery));
        $content = $response->getBody()->getContents();

        if (!preg_match_all('/data-props=\'(?<json>[\s\S]*)\'/imU', $content, $matches)) {
            throw new \RuntimeException(sprintf('Could not fetch data for "%s" in region %s', $row->getTitle(), $row->getRegion()));
        }

        $results = [];
        foreach ($matches['json'] as $match) {
            $results[] = json_decode(html_entity_decode($match), true);
        }

        foreach ($results as $result) {
            if ($this->sanitizeString($result['name']) !== $this->sanitizeString($row->getTitle()) &&
                $this->sanitizeString($result['name']) !== $this->sanitizeString($row->getTitle() . ' PS4 And PS5') &&
                $this->sanitizeString($result['name']) !== $this->sanitizeString($row->getTitle() . ' PS4 & PS5')) {
                continue;
            }

            if (empty($result['float_price'])) {
                continue;
            }

            $amount = (int)($result['float_price'] * 100);

            return new Money($amount, $currency);
        }

        throw new \RuntimeException(sprintf('Could not determine price for "%s"', $row->getTitle()));
    }

    private function sanitizeString(string $string): string
    {
        $string = strtolower(str_replace([':', '-', '—', '(', ')', '[', ']'], '', $string));
        $string = str_replace('&', 'and', $string);
        // Replace multiple spaces with one.
        return preg_replace('/[\s]{2,}/im', ' ', $string);
    }


}