<?php

namespace App;

use Carbon\CarbonInterval;
use GuzzleHttp\Client;

class TrophyFetcher
{
    public const JSON_FILE = 'easy-platinums.json';
    private const PROFILE_NAME = 'Fluttezuhher';

    public function __construct(
        private Client $client,
        private FileContentsWrapper $fileContentsWrapper,
    )
    {
    }

    public function doFetch(): void
    {
        $response = $this->client->get('https://psnprofiles.com/' . self::PROFILE_NAME . '?ajax=1&page=0');

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
                'uri' => '/<a class="title" href="(?<value>[\S]*)\/' . self::PROFILE_NAME . '" rel="nofollow">/im',
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

            $approxTime = ceil(abs(((new \DateTime($matches['platinumTime']))->getTimestamp() - (new \DateTime())->getTimestamp())) / 60);
            if($approxTime > 60){
                // Takes too much time to obtain platinum, skip.
                continue;
            }

            $json[$matches['id']] = [
                'id' => $matches['id'],
                'title' => html_entity_decode($matches['title']),
                'region' => $matches['region'],
                'platform' => $matches['platform'],
                'thumbnail' => 'https://i.psnprofiles.com/games/' . $matches['thumb'],
                'uri' => 'https://psnprofiles.com' . $matches['uri'],
                'approximateTime' => $approxTime. ' min',
                'trophiesTotal' => $matches['trophiesTotal'],
                'trophiesGold' => $matches['trophiesGold'],
                'trophiesSilver' => $matches['trophiesSilver'],
                'trophiesBronze' => $matches['trophiesBronze'],
                'points' => ($matches['trophiesBronze'] * 15) + ($matches['trophiesSilver'] * 30) + ($matches['trophiesGold'] * 90) + 300,
            ];
        }

        $this->fileContentsWrapper->put(self::JSON_FILE, json_encode($json));
    }
}