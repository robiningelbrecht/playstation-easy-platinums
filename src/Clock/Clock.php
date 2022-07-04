<?php

namespace App\Clock;

interface Clock
{
    public const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    public function getCurrentDateTimeImmutable(): \DateTimeImmutable;
}