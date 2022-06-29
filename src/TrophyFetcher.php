<?php

namespace App;

use GuzzleHttp\Client;

class TrophyFetcher
{
    private const PROFILE_NAME = 'Fluttezuhher';

    public function __construct(
        private Client $client
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

        $json = [];
        foreach ($rows['games'] as $game) {
            preg_match('/href=[\S]*"\/trophies\/(?<value>[0-9]*)-[\S]*"/im', $game, $id);
            if (empty($id['value'])) {
                continue;
            }

            preg_match('/Platinum[\s]*in <b>(?<value>.*?)<\/b>/im', $game, $platinumTime);
            if (empty($platinumTime['value'])) {
                continue;
            }

            preg_match('/<a class="title"[\s\S]*>(?<value>.*?)<\/a>/im', $game, $title);
            if (empty($title['value'])) {
                continue;
            }

            preg_match('/<img src="https:\/\/i.psnprofiles.com\/games\/(?<value>[\S]*)" \/>/im', $game, $thumb);
            if (empty($thumb['value'])) {
                continue;
            }

            preg_match('/<a class="title" href="(?<value>[\S]*)\/' . self::PROFILE_NAME . '" rel="nofollow">/im', $game, $uri);
            if (empty($uri['value'])) {
                continue;
            }
            preg_match('/<\/bullet>[\s]*(?<value>[\S]*)[\s]*<\/span>/imU', $game, $region);
            if (empty($region['value'])) {
                continue;
            }

            preg_match('/<span class="tag platform[\s\S]*">(?<value>[\S]*)<\/span>/imU', $game, $platform);
            if (empty($platform['value'])) {
                continue;
            }

            $json[] = [
                'id' => $id['value'],
                'title' => $title['value'],
                'region' => $region['value'],
                'platform' => $platform['value'],
                'thumbnail' => 'https://i.psnprofiles.com/games/' . $thumb['value'],
                'uri' => 'https://psnprofiles.com' . $uri['value'],
                'approximateTime' => '3 min',
                'trophiesTotal' => 21,
                'trophiesGold' => 10,
                'trophiesSilver' => 2,
                'trophiesBronze' => 10,
                'points' => 1000,
            ];
        }

        file_put_contents('easy-platinums.json', json_encode($json));
    }
}