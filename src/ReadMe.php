<?php

namespace App;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ReadMe
{
    public const README_FILE = 'README.md';

    public function __construct(
        private FileContentsWrapper $fileContentsWrapper
    )
    {
    }

    public function update(): void
    {
        if (!file_exists(self::README_FILE)) {
            throw new \RuntimeException('README.md not found');
        }
        if (!file_exists(TrophyFetcher::JSON_FILE)) {
            throw new \RuntimeException('easy-platinums.json not found. Run "fetch" first');
        }

        $loader = new FilesystemLoader(dirname(__DIR__) . '/templates');
        $twig = new Environment($loader);

        $template = $twig->load('table.html.twig');
        $render = $template->render([
            'rows' => array_values(json_decode(file_get_contents(TrophyFetcher::JSON_FILE), true)),
        ]);

        $content = preg_replace(
            '/<!-- start easy-platinums -->([\s\S]*)<!-- end easy-platinums -->/imU',
            '<!-- start easy-platinums -->' . PHP_EOL . $render . PHP_EOL . '<!-- end easy-platinums -->',
            $this->fileContentsWrapper->get(self::README_FILE)
        );

        $this->fileContentsWrapper->put(self::README_FILE, $content);
    }
}