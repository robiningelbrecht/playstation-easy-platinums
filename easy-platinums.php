<?php

if (php_sapi_name() !== 'cli') {
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use Ahc\Cli\Application;
use App\Clock\SystemClock;
use App\GameFetcher;
use App\FileWriter;
use App\FileContentsWrapper;
use App\PriceFetcher;
use App\PriceUpdater;
use \App\Result\Row;
use GuzzleHttp\Client;

$app = new Application('Easy platinums', '0.0.1');

$app
    ->command('games:fetch', 'Fetch new easy platinums and store in json file')
    ->argument('<profile-names>', 'PSN Profiles to use to determine easy platinums')
    ->action(function ($profileNames) {
        $client = new Client();
        $gameFetcher = (new GameFetcher(
            $client,
            new FileContentsWrapper(),
            new PriceFetcher($client),
            new SystemClock(),
        ));

        $addedRows = [];
        foreach (explode(',', $profileNames) as $profileName) {
            $addedRows = [...$addedRows, ...$gameFetcher->doFetch($profileName)];
        }

        if (!$addedRows) {
            return;
        }

        echo sprintf(
            'Added %s new games to list: %s',
            count($addedRows),
            implode(', ', array_map(fn(Row $row) => $row->getFullTitle(), $addedRows))
        );
    })
    ->tap()
    ->command('files:update', 'Update list of games')
    ->action(function () {
        (new FileWriter(
            new FileContentsWrapper(),
            new SystemClock()
        ))->writePages();
    })
    ->tap()
    ->command('price:set', 'Set price of one game')
    ->argument('<id>', 'PSN Profile game id to set price for')
    ->argument('<amountInCents>', 'The price in cents')
    ->action(function (string $id, int $amountInCents) {
        $updatedRow = (new PriceUpdater(
            new FileContentsWrapper(),
        ))->doUpdateForId($id, $amountInCents);

        echo sprintf(
            'Manual price update for %s to %s via workflow',
            $updatedRow->getFullTitle(),
            $updatedRow->getPriceFormattedAsMoney()
        );
    });

$app->logo('
███████╗ █████╗ ███████╗██╗   ██╗    ██████╗ ██╗      █████╗ ████████╗██╗███╗   ██╗██╗   ██╗███╗   ███╗███████╗
██╔════╝██╔══██╗██╔════╝╚██╗ ██╔╝    ██╔══██╗██║     ██╔══██╗╚══██╔══╝██║████╗  ██║██║   ██║████╗ ████║██╔════╝
█████╗  ███████║███████╗ ╚████╔╝     ██████╔╝██║     ███████║   ██║   ██║██╔██╗ ██║██║   ██║██╔████╔██║███████╗
██╔══╝  ██╔══██║╚════██║  ╚██╔╝      ██╔═══╝ ██║     ██╔══██║   ██║   ██║██║╚██╗██║██║   ██║██║╚██╔╝██║╚════██║
███████╗██║  ██║███████║   ██║       ██║     ███████╗██║  ██║   ██║   ██║██║ ╚████║╚██████╔╝██║ ╚═╝ ██║███████║
╚══════╝╚═╝  ╚═╝╚══════╝   ╚═╝       ╚═╝     ╚══════╝╚═╝  ╚═╝   ╚═╝   ╚═╝╚═╝  ╚═══╝ ╚═════╝ ╚═╝     ╚═╝╚══════╝                                                                                                            
');
$app->handle($_SERVER['argv']);
