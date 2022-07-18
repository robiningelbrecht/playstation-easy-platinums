<?php

namespace App;

use App\Clock\Clock;
use App\Result\Row;
use Money\Money;

class ManualUpdater
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly Clock $clock
    )
    {
    }

    public function updatePriceForId(string $id, int $amountInCents): Row
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

    public function removeGameById(string $id): Row{
        if (!file_exists(GameRepository::JSON_FILE)) {
            throw new \RuntimeException('easy-platinums.json not found. Run "fetch" first');
        }

        $json = $this->gameRepository->find($id);
        $json['removedOn'] = $this->clock->getCurrentDateTimeImmutable()->format(Clock::DEFAULT_FORMAT);
        $this->gameRepository->save($json);

        return Row::fromArray($json);
    }
}