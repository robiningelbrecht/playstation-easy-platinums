<?php

use App\Clock\Clock;
use App\Clock\SystemClock;
use App\GameRepository;
use SleekDB\Store;

return [
    Clock::class => DI\create(SystemClock::class),
    Store::class => new Store('games', GameRepository::SLEEKDB_DIRECTORY, [
        'primary_key' => 'id',
        'timeout' => false,
    ]),
];