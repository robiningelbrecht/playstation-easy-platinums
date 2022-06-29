<?php

namespace App;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ReadMe
{
    private const README_FILE = 'README.md';
    private const JSON_FILE = 'easy-platinums.json';

    public function update(): void
    {
        if (!file_exists(self::README_FILE)) {
            throw new \RuntimeException('README.md not found');
        }
        if (!file_exists(self::JSON_FILE)) {
            throw new \RuntimeException('easy-platinums.json not found. Run "fetch" first');
        }

        $loader = new FilesystemLoader(dirname(__DIR__) . '/templates');
        $twig = new Environment($loader);

        $template = $twig->load('table.html.twig');
        $render = $template->render([
            'rows'=> json_decode(file_get_contents(self::JSON_FILE))
        ]);

        $content = preg_replace(
            '/<!-- start easy-platinums -->([\s\S]*)<!-- end easy-platinums -->/imU',
            '<!-- start easy-platinums -->' . PHP_EOL . $render . PHP_EOL . '<!-- end easy-platinums -->',
            file_get_contents(self::README_FILE)
        );

        file_put_contents(self::README_FILE, $content);
    }
}