<?php

namespace App\Statistics;

use App\Result\ResultSet;

class MonthlyStatistics
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
        $now = new \DateTimeImmutable('now');

        $today = new Row('Today');
        foreach ($this->resultSet->getRows() as $row) {
            $statistic = $statistics[$row->getAddedOn()->format('Ym')] ?? new Row($row->getAddedOn()->format('F Y'));

            $statistic
                ->incrementNumberOfGames()
                ->addToNumberOfTrophies($row->getTrophiesTotal())
                ->addToPoints($row->getPoints());

            $statistics[$row->getAddedOn()->format('Ym')] = $statistic;

            if ($row->getAddedOn()->format('Ymd') !== $now->format('Ymd')) {
                $today
                    ->incrementNumberOfGames()
                    ->addToNumberOfTrophies($row->getTrophiesTotal())
                    ->addToPoints($row->getPoints());
            }
        }

        return [$today, ...$statistics];
    }

    public function getTotals(): Row
    {
        $totals = new Row('Totals');
        foreach ($this->resultSet->getRows() as $row) {
            $totals
                ->incrementNumberOfGames()
                ->addToNumberOfTrophies($row->getTrophiesTotal())
                ->addToPoints($row->getPoints());
        }

        return $totals;
    }

}