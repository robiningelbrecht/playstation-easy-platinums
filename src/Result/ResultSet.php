<?php

namespace App\Result;

use App\Filter\Filter;
use App\Filter\FilterField;
use App\Sort\SortDirection;
use App\Sort\SortField;
use App\Sort\Sorting;

class ResultSet
{
    private const INITIAL_IMPORT_DATE = '2022-07-04';

    private function __construct(
        private array $rows
    )
    {
        // Remove duplicate rows. Apparently some games are returned multiple times.
        $this->removeDuplicateAndFaultyEntries();
    }

    public function sort(Sorting $sorting): void
    {
        switch ($sorting->getSortField()) {
            case SortField::PRICE:
            case SortField::POINTS:
            case SortField::TROPHIES:
            case SortField::TIME:
                usort(
                    $this->rows,
                    function (Row $a, Row $b) use ($sorting) {
                        $sortField = $sorting->getSortField();
                        if ($a->getValueForSortField($sortField) === $b->getValueForSortField($sortField)) {
                            return 0;
                        }

                        if ($sorting->getSortDirection() === SortDirection::ASC) {
                            // We want to push null values to the back of the list.
                            $aValue = $a->getValueForSortField($sortField) !== null ? $a->getValueForSortField($sortField) : 99999;
                            $bValue = $b->getValueForSortField($sortField) !== null ? $b->getValueForSortField($sortField) : 99999;
                            return ($aValue < $bValue) ? -1 : 1;
                        }

                        // We want to push null values to the back of the list.
                        $aValue = $a->getValueForSortField($sortField) !== null ? $a->getValueForSortField($sortField) : -99999;
                        $bValue = $b->getValueForSortField($sortField) !== null ? $b->getValueForSortField($sortField) : -99999;
                        return ($aValue > $bValue) ? -1 : 1;
                    }
                );
                break;
            case SortField::DATE:
                usort(
                    $this->rows,
                    function (Row $a, Row $b) use ($sorting) {
                        $sortField = $sorting->getSortField();

                        $aDate = $a->getValueForSortField($sortField);
                        $bDate = $b->getValueForSortField($sortField);

                        // Because we imported most of the initial games on the 4th of july,
                        // We're going to do some funky stuff here.
                        if ($aDate->format('Y-m-d') === self::INITIAL_IMPORT_DATE && $bDate->format('Y-m-d') !== self::INITIAL_IMPORT_DATE) {
                            return $sorting->getSortDirection() === SortDirection::ASC ? -1 : 1;
                        }
                        if ($aDate->format('Y-m-d') !== self::INITIAL_IMPORT_DATE && $bDate->format('Y-m-d') === self::INITIAL_IMPORT_DATE) {
                            return $sorting->getSortDirection() === SortDirection::ASC ? 1 : -1;
                        }

                        if ($aDate->format('Y-m-d') === self::INITIAL_IMPORT_DATE && $bDate->format('Y-m-d') === self::INITIAL_IMPORT_DATE) {
                            // We will take the ID into account here.
                            if ($a->getId() === $b->getId()) {
                                return 0;
                            }

                            if ($sorting->getSortDirection() === SortDirection::ASC) {
                                return ($a->getId() < $b->getId()) ? -1 : 1;
                            }

                            return ($a->getId() > $b->getId()) ? -1 : 1;
                        }

                        // Starting from here it's just normal sorting on date.
                        $aDateValue = strtotime($aDate->format('Y-m-d H:i:s'));
                        $bDateValue = strtotime($bDate->format('Y-m-d H:i:s'));
                        if ($sorting->getSortDirection() === SortDirection::ASC) {
                            return ($aDateValue < $bDateValue) ? -1 : 1;
                        }

                        return ($aDateValue > $bDateValue) ? -1 : 1;
                    }
                );
                break;
            default:
                usort(
                    $this->rows,
                    function (Row $a, Row $b) use ($sorting) {
                        $sortField = $sorting->getSortField();

                        if ($sorting->getSortDirection() === SortDirection::ASC) {
                            return strcmp($a->getValueForSortField($sortField), $b->getValueForSortField($sortField));
                        }
                        return strcmp($b->getValueForSortField($sortField), $a->getValueForSortField($sortField));
                    }
                );
        }
    }

    /**
     * @return Row[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    public function getRowsForFilter(Filter $filter): array
    {
        return array_filter(
            $this->rows,
            fn(Row $row) => $row->getValueForFilterField($filter->getFilterField()->getName()) == $filter->getValue()
        );
    }

    private function removeDuplicateAndFaultyEntries(): void
    {
        $keys = [];

        foreach ($this->rows as $delta => $row) {
            /** @var Row $row */
            if ($row->getApproximateTime() === 0) {
                unset($this->rows[$delta]);
                continue;
            }

            if (!in_array($row->getUniqueValue(), $keys)) {
                $keys[] = $row->getUniqueValue();
                continue;
            }

            unset($this->rows[$delta]);
        }
    }

    public static function fromArray(array $rows): self
    {
        $rows = array_map(
            fn(array $row) => Row::fromArray($row),
            $rows
        );
        return new self($rows);
    }
}