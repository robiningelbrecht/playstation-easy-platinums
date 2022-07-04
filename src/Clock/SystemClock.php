<?php

namespace App\Clock;

class SystemClock implements Clock
{
    public function getCurrentDateTimeImmutable(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('Europe/Brussels'));
    }
}