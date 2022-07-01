<?php

if (php_sapi_name() !== 'cli') {
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use Ahc\Cli\Application;
use App\TrophyFetcher;
use App\Writer;
use App\FileContentsWrapper;
use GuzzleHttp\Client;

$app = new Application('Easy platinums', '0.0.1');

$app
    ->command('fetch', 'Fetch new easy platinums and store in json file')
    ->action(function () {
        (new TrophyFetcher(new Client(), new FileContentsWrapper()))->doFetch();;
    })
    ->tap()
    ->command('update', 'Update list of games')
    ->action(function () {
        (new Writer(new FileContentsWrapper()))->writePages();
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
