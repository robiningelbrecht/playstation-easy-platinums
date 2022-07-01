<?php

namespace App;

use GuzzleHttp\Client;

class TrophyFetcher
{
    public const JSON_FILE = 'easy-platinums.json';
    public const DEFAULT_PROFILE_NAME = 'ikemenzi';

    public function __construct(
        private readonly Client $client,
        private readonly FileContentsWrapper $fileContentsWrapper,
        private readonly string $psnProfile
    )
    {
    }

    public function doFetch(): void
    {
        $response = $this->client->get('https://psnprofiles.com/' . $this->psnProfile . '?ajax=1&page=0');

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Could not fetch games');
        }
        $content = json_decode($response->getBody()->getContents(), true);

        if (empty($content['html'])) {
            throw new \RuntimeException('Could not fetch games');
        }

        preg_match_all('/<tr.*?>(?<games>[\s\S]*)<\/tr>/imU', $content['html'], $rows);

        $json = json_decode($this->fileContentsWrapper->get(self::JSON_FILE), true);
        foreach ($rows['games'] as $game) {
            $regexes = [
                'id' => '/href=[\S]*"\/trophies\/(?<value>[0-9]*)-[\S]*"/im',
                'platinumTime' => '/Platinum[\s]*in <b>(?<value>.*?)<\/b>/im',
                'title' => '/<a class="title"[\s\S]*>(?<value>.*?)<\/a>/im',
                'thumb' => '/<img src="https:\/\/i.psnprofiles.com\/games\/(?<value>[\S]*)" \/>/im',
                'uri' => '/<a class="title" href="(?<value>[\S]*)\/' . $this->psnProfile . '" rel="nofollow">/im',
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

            if (count($regexes) !== count($matches)) {
                // Not all regexes were successful skip.
                continue;
            }

            if (array_key_exists($matches['id'], $json)) {
                // Already fetched this game, skip.
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

            // Download thumb.
            $filename = $matches['id'] . '.' . pathinfo($matches['thumb'], PATHINFO_EXTENSION);
            $content = $this->fileContentsWrapper->get('https://i.psnprofiles.com/games/' . $matches['thumb']);
            $this->fileContentsWrapper->put('assets/thumbs/' . $filename, $content);

            $newEntry = [$matches['id'] => [
                'id' => $matches['id'],
                'title' => html_entity_decode($matches['title']),
                'region' => $matches['region'],
                'platform' => $matches['platform'],
                'thumbnail' => $filename,
                'uri' => 'https://psnprofiles.com' . $matches['uri'],
                'approximateTime' => $approxTime . ' min',
                'trophiesTotal' => $matches['trophiesTotal'],
                'trophiesGold' => $matches['trophiesGold'],
                'trophiesSilver' => $matches['trophiesSilver'],
                'trophiesBronze' => $matches['trophiesBronze'],
                'points' => ($matches['trophiesBronze'] * 15) + ($matches['trophiesSilver'] * 30) + ($matches['trophiesGold'] * 90) + 300,
            ]];

            // Prepend new entry to beginning of array.
            $json = $newEntry + $json;
        }

        $this->fileContentsWrapper->put(self::JSON_FILE, json_encode($json));
    }
}