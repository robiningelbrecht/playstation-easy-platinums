<?php

namespace App;

use App\Result\ResultSet;
use App\Sort\SortDirection;
use App\Sort\SortField;
use App\Sort\Sorting;
use App\Sort\SortingHelper;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class FileWriter
{
    public const README_FILE = 'README.md';
    private const PAGE_SIZE = 250;

    public function __construct(
        private readonly FileContentsWrapper $fileContentsWrapper
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
        $twig->addFunction(new TwigFunction('renderSort', [SortingHelper::class, 'renderSort']));
        $template = $twig->load('table.html.twig');

        $resultSet = ResultSet::fromJson($this->fileContentsWrapper->get(TrophyFetcher::JSON_FILE));
        $resultSet->sort(Sorting::default());

        $numberOfPages = ceil(count($resultSet) / self::PAGE_SIZE);
        $rows = $resultSet->getRows();

        // Render the first page on the main README.md.
        $render = $template->render([
            'currentPage' => 1,
            'totalPages' => $numberOfPages,
            'rows' => array_slice($rows, 0, self::PAGE_SIZE),
            'sorting' => Sorting::default(),
        ]);

        $readMeContent = preg_replace(
            '/<!-- start easy-platinums -->([\s\S]*)<!-- end easy-platinums -->/im',
            '<!-- start easy-platinums -->' . PHP_EOL . $render . PHP_EOL . '<!-- end easy-platinums -->',
            $this->fileContentsWrapper->get(self::README_FILE)
        );

        $this->fileContentsWrapper->put(self::README_FILE, $readMeContent);

        // Render all pages for all possible sorts.
        foreach (SortField::cases() as $sortField) {
            foreach (SortDirection::cases() as $sortDirection) {
                // @TODO: Sort rows.
                for ($i = 0; $i < $numberOfPages; $i++) {
                    $render = $template->render([
                        'currentPage' => $i + 1,
                        'totalPages' => $numberOfPages,
                        'rows' => array_slice($rows, ($i * self::PAGE_SIZE), self::PAGE_SIZE),
                        'sorting' => Sorting::fromFieldAndDirection($sortField, $sortDirection),
                    ]);

                    $content = preg_replace(
                        '/<!-- start easy-platinums -->([\s\S]*)<!-- end easy-platinums -->/im',
                        '<!-- start easy-platinums -->' . PHP_EOL . $render . PHP_EOL . '<!-- end easy-platinums -->',
                        $readMeContent
                    );

                    $this->fileContentsWrapper->put('public/PAGE-' . ($i + 1) . '-SORT_' . $sortField->toUpper() . '_' . $sortDirection->toUpper() . '.md', $content);
                }
            }
        }
    }
}