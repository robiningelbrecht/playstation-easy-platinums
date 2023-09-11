<?php

namespace App\Clock;

readonly class PausedClock implements Clock
{
    private function __construct(
        private \DateTimeImmutable $pausedOn
    )
    {

    }

    public static function on(\DateTimeImmutable $on): PausedClock
    {
        return new self($on);
    }

    public function getCurrentDateTimeImmutable(): \DateTimeImmutable
    {
        return $this->pausedOn;
    }
}