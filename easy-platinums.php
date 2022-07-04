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
use GuzzleHttp\Client;

$app = new Application('Easy platinums', '0.0.1');

$app
    ->command('fetch', 'Fetch new easy platinums and store in json file')
    ->argument('<profile-name>', 'PSN Profile to use to determine easy platinums', GameFetcher::DEFAULT_PROFILE_NAME)
    ->action(function ($profileName) {
        $client = new Client();
        (new GameFetcher(
            $client,
            new FileContentsWrapper(),
            new PriceFetcher($client),
            new SystemClock(),
            $profileName
        ))->doFetch();;
    })
    ->tap()
    ->command('update', 'Update list of games')
    ->action(function () {
        (new FileWriter(new FileContentsWrapper()))->writePages();
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
