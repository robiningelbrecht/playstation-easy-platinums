<?php

namespace App;

use App\Clock\Clock;
use App\Result\Row;
use GuzzleHttp\Client;

readonly class GameFetcher
{

    public function __construct(
        private Client $client,
        private FileContentsWrapper $fileContentsWrapper,
        private GameRepository $gameRepository,
        private PriceFetcher $priceFetcher,
        private Clock $clock,
    )
    {
    }

    public function doFetch($psnProfile): array
    {
        $addedRows = [];
        $response = $this->client->get('https://psnprofiles.com/' . $psnProfile . '?ajax=1&page=0');

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Could not fetch games for profile ' . $psnProfile);
        }
        $content = json_decode($response->getBody()->getContents(), true);

        if (empty($content['html'])) {
            throw new \RuntimeException('Could not fetch games for profile ' . $psnProfile);
        }

        preg_match_all('/<tr.*?>(?<games>[\s\S]*)<\/tr>/imU', $content['html'], $rows);

        foreach ($rows['games'] as $game) {
            $regexes = [
                'id' => '/href=[\S]*"\/trophies\/(?<value>[0-9]*)-[\S]*"/im',
                'platinumTime' => '/Platinum[\s]*in <b>(?<value>.*?)<\/b>/im',
                'title' => '/<a class="title"[\s\S]*>(?<value>.*?)<\/a>/im',
                'thumb' => '/<img src="https:\/\/i.psnprofiles.com\/games\/(?<value>[\S]*)" \/>/im',
                'uri' => '/<a class="title" href="(?<value>[\S]*)\/' . $psnProfile . '" rel="nofollow">/im',
                'region' => '/<\/bullet>[\s]*(?<value>[\S]*)[\s]*<\/span>/imU',
                'platform' => '/<span class="tag platform[\s\S]*">(?<value>[\S]*)<\/span>/imU',
                'trophiesTotal' => '/All[\s]*<b>(?<value>[\d]+)<\/b> Trophies/imU',
                'trophiesGold' => '/<span class="icon-sprite gold"><\/span><span>(?<value>[\d]+)<\/span>/imU',
                'trophiesSilver' => '/<span class="icon-sprite silver"><\/span><span>(?<value>[\d]+)<\/span>/imU',
                'trophiesBronze' => '/<span class="icon-sprite bronze"><\/span><span>(?<value>[\d]+)<\/span>/imU',
            ];

            $matches = [];
            foreach ($regexes as $field => $regex) {
                if (!preg_match($regex, $game, $match)) {
                    continue;
                }

                $matches[$field] = $match['value'];
            }

            if (0 !== count(array_diff($this->getRequiredRegexMatches(), array_keys($matches)))) {
                // Not all required regexes were successful skip.
                continue;
            }

            try {
                if ($this->gameRepository->findIncludingRemoved($matches['id'])) {
                    // Already fetched this game in a previous run, skip.
                    continue;
                }
            } catch (\RuntimeException) {

            }

            if (array_key_exists($matches['id'], $addedRows)) {
                // Already fetched this game in the current run, skip.
                continue;
            }

            if (!in_array($matches['platform'], ['PS4', 'PS5'])) {
                // Not interested in very old games, skip.
                continue;
            }

            $approxTime = ceil(abs(((new \DateTime($matches['platinumTime']))->getTimestamp() - (new \DateTime())->getTimestamp())) / 60);
            if ($approxTime > 60) {
                // Takes too much time to obtain platinum, skip.
                continue;
            }

            if ($approxTime == 0) {
                // Something is wrong, skip.
                continue;
            }

            // Download thumb.
            $filename = $matches['id'] . '.' . pathinfo($matches['thumb'], PATHINFO_EXTENSION);
            $content = $this->fileContentsWrapper->get('https://i.psnprofiles.com/games/' . $matches['thumb']);
            $this->fileContentsWrapper->put('assets/thumbs/' . $filename, $content);

            $addedRows[$matches['id']] = [
                'id' => $matches['id'],
                'title' => html_entity_decode($matches['title']),
                'region' => $matches['region'] ?? null,
                'platform' => $matches['platform'],
                'thumbnail' => $filename,
                'uri' => 'https://psnprofiles.com' . $matches['uri'],
                'approximateTime' => (int)$approxTime,
                'trophiesTotal' => (int)$matches['trophiesTotal'],
                'trophiesGold' => (int)$matches['trophiesGold'],
                'trophiesSilver' => (int)$matches['trophiesSilver'],
                'trophiesBronze' => (int)$matches['trophiesBronze'],
                'addedOn' => $this->clock->getCurrentDateTimeImmutable()->format(Clock::DEFAULT_FORMAT),
            ];

            try {
                $addedRows[$matches['id']]['price'] = $this->priceFetcher->searchForRow(Row::fromArray($addedRows[$matches['id']]));
            } catch (\RuntimeException) {
                $addedRows[$matches['id']]['price'] = null;
            }
        }

        $this->gameRepository->saveMany($addedRows);
        return array_map(fn(array $row) => Row::fromArray($row), $addedRows);
    }

    private function getRequiredRegexMatches(): array
    {
        return [
            'id',
            'platinumTime',
            'title',
            'thumb',
            'uri',
            'platform',
            'trophiesTotal',
            'trophiesGold',
            'trophiesSilver',
            'trophiesBronze',
        ];
    }
}