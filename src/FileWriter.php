<?php

namespace App;

use App\Result\ResultSet;
use App\Sort\SortDirection;
use App\Sort\SortField;
use App\Sort\Sorting;
use App\Sort\SortingHelper;
use App\Statistics\Statistics;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class FileWriter
{
    public const README_FILE = 'README.md';
    public const STATISTICS_FILE = 'public/STATISTICS.md';

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
        if (!file_exists(GameFetcher::JSON_FILE)) {
            throw new \RuntimeException('easy-platinums.json not found. Run "fetch" first');
        }

        $loader = new FilesystemLoader(dirname(__DIR__) . '/templates');
        $twig = new Environment($loader);
        $twig->addFunction(new TwigFunction('renderSort', [SortingHelper::class, 'renderSort']));
        $template = $twig->load('games.html.twig');

        $resultSet = ResultSet::fromJson($this->fileContentsWrapper->get(GameFetcher::JSON_FILE));
        $resultSet->sort(Sorting::default());

        // Render the first page on the main README.md.
        $this->fileContentsWrapper->put(self::README_FILE, $template->render([
            'paging' => Paging::fromResultSetAndCurrentPage($resultSet, 1),
            'rows' => array_slice($resultSet->getRows(), 0, Paging::PAGE_SIZE),
            'sorting' => Sorting::default(),
        ]));

        // Render all pages for all possible sorts.
        foreach (SortField::cases() as $sortField) {
            foreach (SortDirection::cases() as $sortDirection) {
                $sorting = Sorting::fromFieldAndDirection($sortField, $sortDirection);
                $resultSet->sort($sorting);
                $rows = $resultSet->getRows();

                for ($i = 0; $i < Paging::calculateTotalPages($resultSet); $i++) {
                    $paging = Paging::fromResultSetAndCurrentPage($resultSet, $i + 1);
                    $render = $template->render([
                        'paging' => $paging,
                        'rows' => array_slice($rows, ($i * Paging::PAGE_SIZE), Paging::PAGE_SIZE),
                        'sorting' => $sorting,
                    ]);

                    $this->fileContentsWrapper->put('public/PAGE-' . ($i + 1) . '-SORT_' . $sortField->toUpper() . '_' . $sortDirection->toUpper() . '.md', $render);
                }
            }
        }

        // Render the statistics page.
        $template = $twig->load('statistics.html.twig');
        $this->fileContentsWrapper->put(self::STATISTICS_FILE, $template->render([
            'statistics' => Statistics::fromResultSet($resultSet),
        ]));
    }
}