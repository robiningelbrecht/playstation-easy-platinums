<?php

namespace App\Statistics;

use App\Result\ResultSet;

class Statistics
{
    private function __construct(
        private readonly ResultSet $resultSet
    )
    {
    }

    public static function fromResultSet(ResultSet $resultSet): self
    {
        return new self($resultSet);
    }

    public function getRows(): array
    {
        $statistics = [];

        foreach ($this->resultSet->getRows() as $row) {
            $statistic = $statistics[$row->getAddedOn()->format('Ym')] ?? new Row($row->getAddedOn());

            $statistic
                ->incrementNumberOfGames()
                ->addToNumberOfTrophies($row->getTrophiesTotal())
                ->addToPoints($row->getPoints());

            $statistics[$row->getAddedOn()->format('Ym')] = $statistic;
        }

        return $statistics;
    }

    public function getToday(): Row
    {
        $now = new \DateTimeImmutable('now');
        $today = new Row($now);

        foreach ($this->resultSet->getRows() as $row) {
            if ($row->getAddedOn()->format('Ymd') !== $now->format('Ymd')) {
                continue;
            }

            $today
                ->incrementNumberOfGames()
                ->addToNumberOfTrophies($row->getTrophiesTotal())
                ->addToPoints($row->getPoints());
        }

        return $today;
    }

    public function getTotals(): Row
    {
        $totals = new Row(new \DateTimeImmutable('2022-01-01'));
        foreach ($this->resultSet->getRows() as $row) {
            $totals
                ->incrementNumberOfGames()
                ->addToNumberOfTrophies($row->getTrophiesTotal())
                ->addToPoints($row->getPoints());
        }

        return $totals;
    }

}