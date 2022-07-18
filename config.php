<?php

use App\Clock\Clock;
use App\Clock\SystemClock;

return [
    Clock::class => DI\create(SystemClock::class)
];