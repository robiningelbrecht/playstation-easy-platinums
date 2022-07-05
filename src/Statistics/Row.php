<?php

namespace App\Statistics;

class Row
{
    private int $numberOfGames;
    private int $numberOfTrophies;
    private int $points;

    public function __construct(
        private readonly string $label
    )
    {
        $this->numberOfGames = 0;
        $this->numberOfTrophies = 0;
        $this->points = 0;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getNumberOfGames(): int
    {
        return $this->numberOfGames;
    }

    public function getNumberOfTrophies(): int
    {
        return $this->numberOfTrophies;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function incrementNumberOfGames(): self
    {
        $this->numberOfGames++;

        return $this;
    }

    public function addToNumberOfTrophies(int $num): self
    {
        $this->numberOfTrophies += $num;

        return $this;
    }

    public function addToPoints(int $num): self
    {
        $this->points += $num;

        return $this;
    }
}