<?php

namespace App\Clock;

interface Clock
{
    public function getCurrentDateTimeImmutable(): \DateTimeImmutable;
}