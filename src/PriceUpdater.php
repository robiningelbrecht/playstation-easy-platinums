<?php

namespace App;

use App\Result\Row;
use Money\Money;

class PriceUpdater
{
    public function __construct(
        private readonly GameRepository $gameRepository
    )
    {
    }

    public function doUpdateForId(string $id, int $amountInCents): Row
    {
        if (!file_exists(GameRepository::JSON_FILE)) {
            throw new \RuntimeException('easy-platinums.json not found. Run "fetch" first');
        }

        $json = $this->gameRepository->find($id);
        $row = Row::fromArray($json);

        $json['price'] = new Money($amountInCents, PriceFetcher::getCurrencyForRegion($row->getRegion()));
        $this->gameRepository->save($json);

        return Row::fromArray(json_decode(json_encode($json), true));
    }
}