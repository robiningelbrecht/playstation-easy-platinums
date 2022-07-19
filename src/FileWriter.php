<?php

namespace App;

use App\Clock\Clock;
use App\Filter\Filter;
use App\Filter\FilterField;
use App\Result\ResultSet;
use App\Sort\SortDirection;
use App\Sort\SortField;
use App\Sort\Sorting;
use App\Statistics\MonthlyStatistics;
use App\Statistics\PlatformRegionMatrix;
use App\Twig\TwigRenderSort;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class FileWriter
{
    public const README_FILE = 'README.md';
    public const STATISTICS_FILE = 'public/STATISTICS.md';

    public function __construct(
        private readonly FileContentsWrapper $fileContentsWrapper,
        private readonly GameRepository $gameRepository,
        private readonly Clock $clock,
    )
    {
    }

    public function writePages(): void
    {
        if (!file_exists(self::README_FILE)) {
            throw new \RuntimeException('README.md not found');
        }
        if (!file_exists(GameRepository::JSON_FILE)) {
            throw new \RuntimeException('easy-platinums.json not found. Run "fetch" first');
        }

        $loader = new FilesystemLoader(dirname(__DIR__) . '/templates');
        $twig = new Environment($loader);
        $twig->addFunction(new TwigFunction('renderSort', [TwigRenderSort::class, 'execute']));
        $template = $twig->load('games.html.twig');

        $resultSet = ResultSet::fromArray($this->gameRepository->findAll());
        $resultSet->sort(Sorting::default());

        /** @var FilterField[] $filterFields */
        $filterFields = [
            FilterField::fromNameAndPossibleValues(FilterField::FIELD_REGION, $resultSet->getDistinctValuesForFilterField(FilterField::FIELD_REGION)),
            FilterField::fromNameAndPossibleValues(FilterField::FIELD_PLATFORM, $resultSet->getDistinctValuesForFilterField(FilterField::FIELD_PLATFORM)),
        ];

        // Render the first page on the main README.md.
        $this->fileContentsWrapper->put(self::README_FILE, $template->render([
            'paging' => Paging::fromTotalRowCountAndCurrentPage(count($resultSet->getRows()), 1),
            'rows' => array_slice($resultSet->getRows(), 0, Paging::PAGE_SIZE),
            'sorting' => Sorting::default(),
            'filterFields' => $filterFields,
            'filter' => null,
        ]));

        // Render all pages for all possible sorts.
        foreach (SortField::cases() as $sortField) {
            foreach (SortDirection::cases() as $sortDirection) {
                $sorting = Sorting::fromFieldAndDirection($sortField, $sortDirection);
                $resultSet->sort($sorting);
                $rows = $resultSet->getRows();

                for ($i = 0; $i < Paging::calculateTotalPages(count($rows)); $i++) {
                    $paging = Paging::fromTotalRowCountAndCurrentPage(count($rows), $i + 1);
                    $render = $template->render([
                        'paging' => $paging,
                        'rows' => array_slice($rows, ($i * Paging::PAGE_SIZE), Paging::PAGE_SIZE),
                        'sorting' => $sorting,
                        'filterFields' => $filterFields,
                        'filter' => null,
                    ]);

                    $this->fileContentsWrapper->put(
                        sprintf('public/PAGE-%s-SORT_%s_%s.md', ($i + 1), $sortField->toUpper(), $sortDirection->toUpper()),
                        $render
                    );
                }

                // Render all pages for the available filers.
                foreach ($filterFields as $filterField) {
                    foreach ($filterField->getPossibleValues() as $filterValue) {
                        if ($filterValue === FilterField::ALL_VALUE) {
                            // Pages with all rows have already been generated, skip.
                            continue;
                        }

                        $filter = Filter::fromFilterFieldAndValue($filterField, $filterValue);
                        $rows = $resultSet->getRowsForFilter($filter);

                        for ($i = 0; $i < Paging::calculateTotalPages(count($rows)); $i++) {
                            $paging = Paging::fromTotalRowCountAndCurrentPage(count($rows), $i + 1);
                            $render = $template->render([
                                'paging' => $paging,
                                'rows' => array_slice($rows, ($i * Paging::PAGE_SIZE), Paging::PAGE_SIZE),
                                'sorting' => $sorting,
                                'filterFields' => $filterFields,
                                'filter' => $filter,
                            ]);

                            $this->fileContentsWrapper->put(
                                sprintf(
                                    'public/PAGE-%s-FILTER_%s_%s-SORT_%s_%s.md',
                                    ($i + 1),
                                    $filterField->toUpper(),
                                    $filterValue,
                                    $sortField->toUpper(),
                                    $sortDirection->toUpper()
                                ),
                                $render
                            );
                        }
                    }
                }

            }
        }

        // Render the statistics page.
        $template = $twig->load('statistics.html.twig');
        $this->fileContentsWrapper->put(self::STATISTICS_FILE, $template->render([
            'monthlyStatistics' => MonthlyStatistics::fromResultSet($resultSet, $this->clock->getCurrentDateTimeImmutable()),
            'platformRegionMatrix' => PlatformRegionMatrix::fromResultSet($resultSet),
        ]));
    }
}