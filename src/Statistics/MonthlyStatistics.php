<?php

namespace App\Statistics;

use App\Clock\Clock;
use App\Result\ResultSet;
use App\Sort\Sorting;

class MonthlyStatistics
{
    private function __construct(
        private readonly ResultSet $resultSet,
        private readonly \DateTimeImmutable $now,
    )
    {
    }

    public static function fromResultSet(ResultSet $resultSet, \DateTimeImmutable $now): self
    {
        return new self($resultSet, $now);
    }

    public function getRows(): array
    {
        $statistics = [];
        $yesterdayDate = $this->now->modify('-1 day');

        $this->resultSet->sort(Sorting::default());

        $today = new Row('Today');
        $yesterday = new Row('Yesterday');
        foreach ($this->resultSet->getRows() as $row) {
            $statistic = $statistics[$row->getAddedOn()->format('Ym')] ?? new Row($row->getAddedOn()->format('F Y'));

            $statistic
                ->incrementNumberOfGames()
                ->addToNumberOfTrophies($row->getTrophiesTotal())
                ->addToPoints($row->getPoints());

            $statistics[$row->getAddedOn()->format('Ym')] = $statistic;

            if ($row->getAddedOn()->format('Ymd') === $this->now->format('Ymd')) {
                $today
                    ->incrementNumberOfGames()
                    ->addToNumberOfTrophies($row->getTrophiesTotal())
                    ->addToPoints($row->getPoints());
            }

            if ($row->getAddedOn()->format('Ymd') === $yesterdayDate->format('Ymd')) {
                $yesterday
                    ->incrementNumberOfGames()
                    ->addToNumberOfTrophies($row->getTrophiesTotal())
                    ->addToPoints($row->getPoints());
            }
        }

        return [$today, $yesterday, ...$statistics];
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