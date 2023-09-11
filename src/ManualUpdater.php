<?php

namespace App;

use App\Clock\Clock;
use App\Result\Row;
use Money\Money;

readonly class ManualUpdater
{
    public function __construct(
        private GameRepository $gameRepository,
        private Clock $clock
    )
    {
    }

    public function updatePriceForId(array $ids, int $amountInCents): Row
    {
        if (empty($ids)) {
            throw new \RuntimeException('ids cannot be empty');
        }
        foreach ($ids as $id) {
            $json = $this->gameRepository->find($id);
            $row = Row::fromArray($json);

            $json['price'] = new Money($amountInCents, PriceFetcher::getCurrencyForRegion($row->getRegion()));
            $this->gameRepository->save($json);
        }

        return Row::fromArray(json_decode(json_encode($json), true));
    }

    public function removeGameById(string $id): Row
    {
        $json = $this->gameRepository->find($id);
        $json['removedOn'] = $this->clock->getCurrentDateTimeImmutable()->format(Clock::DEFAULT_FORMAT);
        $this->gameRepository->save($json);

        return Row::fromArray($json);
    }
}