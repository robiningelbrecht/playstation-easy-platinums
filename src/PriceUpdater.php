<?php

namespace App;

use App\Result\Row;

class PriceUpdater
{
    public function __construct(
        private PriceFetcher $priceFetcher,
        private FileContentsWrapper $fileContentsWrapper,
    )
    {
    }

    public function doUpdateForId(string $id): void
    {
        if (!file_exists(GameFetcher::JSON_FILE)) {
            throw new \RuntimeException('easy-platinums.json not found. Run "fetch" first');
        }

        $json = json_decode($this->fileContentsWrapper->get(GameFetcher::JSON_FILE), true);
        if (!array_key_exists($id, $json)) {
            throw new \RuntimeException('Invalid id provided');
        }

        try {
            $json[$id]['price'] = $this->priceFetcher->searchForRow(Row::fromArray($json[$id]));
            $this->fileContentsWrapper->put(GameFetcher::JSON_FILE, json_encode($json));
        } catch (\RuntimeException) {

        }
    }
}