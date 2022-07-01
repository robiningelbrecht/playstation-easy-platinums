<?php

namespace App;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class FileWriter
{
    public const README_FILE = 'README.md';
    private const PAGE_SIZE = 250;

    public function __construct(
        private FileContentsWrapper $fileContentsWrapper
    )
    {
    }

    public function writePages(): void
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

        $rows = json_decode(file_get_contents(TrophyFetcher::JSON_FILE), true);
        // Sort rows by ID DESC. We're assuming that highest id is the newest game.
        krsort($rows, SORT_NUMERIC);
        // Remove duplicate rows. Apparently some games are returned multiple times.
        $this->removeDuplicateEntries($rows);

        $numberOfPages = ceil(count($rows) / self::PAGE_SIZE);

        // Render the first page on the main README.md.
        $render = $template->render([
            'currentPage' => 1,
            'totalPages' => $numberOfPages,
            'rows' => array_slice($rows, 0, self::PAGE_SIZE),
        ]);

        $readMeContent = preg_replace(
            '/<!-- start easy-platinums -->([\s\S]*)<!-- end easy-platinums -->/im',
            '<!-- start easy-platinums -->' . PHP_EOL . $render . PHP_EOL . '<!-- end easy-platinums -->',
            $this->fileContentsWrapper->get(self::README_FILE)
        );

        $this->fileContentsWrapper->put(self::README_FILE, $readMeContent);

        // Now render all pages in a separate folder.
        for ($i = 0; $i < $numberOfPages; $i++) {
            $render = $template->render([
                'currentPage' => $i + 1,
                'totalPages' => $numberOfPages,
                'rows' => array_slice($rows, ($i * self::PAGE_SIZE), self::PAGE_SIZE),
            ]);

            $content = preg_replace(
                '/<!-- start easy-platinums -->([\s\S]*)<!-- end easy-platinums -->/im',
                '<!-- start easy-platinums -->' . PHP_EOL . $render . PHP_EOL . '<!-- end easy-platinums -->',
                $readMeContent
            );

            $this->fileContentsWrapper->put('public/PAGE-' . ($i + 1) . '.md', $content);
        }

    }

    private function removeDuplicateEntries(array &$rows): void
    {
        $keys = [];
        foreach ($rows as $delta => $row) {
            $key = $row['title'] . $row['platform'] . ($row['region'] ?? '');
            if (!in_array($key, $keys)) {
                $keys[] = $key;
                continue;
            }

            unset($rows[$delta]);
        }
    }
}