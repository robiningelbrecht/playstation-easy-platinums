<?php

namespace App\Result;

use App\Sort\SortDirection;
use App\Sort\SortField;
use App\Sort\Sorting;

class ResultSet implements \Countable
{
    private function __construct(
        private array $rows
    )
    {
        // Remove duplicate rows. Apparently some games are returned multiple times.
        $this->removeDuplicateAndFaultyEntries();
    }

    public function sort(Sorting $sorting): void
    {
        if ($sorting->getSortField()->getType() === SortField::TYPE_NUMERIC) {
            usort(
                $this->rows,
                function (Row $a, Row $b) use ($sorting) {
                    $sortField = $sorting->getSortField();
                    if ($a->getValueForSortField($sortField) === $b->getValueForSortField($sortField)) {
                        return 0;
                    }

                    if ($sorting->getSortDirection() === SortDirection::ASC) {
                        $aValue = $a->getValueForSortField($sortField) !== null ? $a->getValueForSortField($sortField) : 99999;
                        $bValue = $b->getValueForSortField($sortField) !== null ? $b->getValueForSortField($sortField) : 99999;
                        return ($aValue < $bValue) ? -1 : 1;
                    }

                    $aValue = $a->getValueForSortField($sortField) !== null ? $a->getValueForSortField($sortField) : -99999;
                    $bValue = $b->getValueForSortField($sortField) !== null ? $b->getValueForSortField($sortField) : -99999;
                    return ($aValue > $bValue) ? -1 : 1;
                }
            );

            return;
        }

        if ($sorting->getSortField()->getType() === SortField::TYPE_DATE) {
            usort(
                $this->rows,
                function (Row $a, Row $b) use ($sorting) {
                    $sortField = $sorting->getSortField();

                    $aDate = $a->getValueForSortField($sortField);
                    $bDate = $b->getValueForSortField($sortField);

                    if ($aDate->format('Y-m-d') === '2022-07-04' && $bDate->format('Y-m-d') !== '2022-07-04') {
                        return 1;
                    }
                    if ($aDate->format('Y-m-d') !== '2022-07-04' && $bDate->format('Y-m-d') === '2022-07-04') {
                        return -1;
                    }

                    if ($aDate->format('Y-m-d') === '2022-07-04' && $bDate->format('Y-m-d') === '2022-07-04') {
                        // Because we imported most of the initial games on the 4th of july,
                        // we will take the ID into account here.
                        if ($a->getId() === $b->getId()) {
                            return 0;
                        }

                        if ($sorting->getSortDirection() === SortDirection::ASC) {
                            return ($a->getId() < $b->getId()) ? -1 : 1;
                        }

                        return ($a->getId() > $b->getId()) ? -1 : 1;
                    }

                    $aDateValue = strtotime($aDate->format('Y-m-d H:i:s'));
                    $bDateValue = strtotime($bDate->format('Y-m-d H:i:s'));
                    if ($sorting->getSortDirection() === SortDirection::ASC) {
                        return ($aDateValue < $bDateValue) ? -1 : 1;
                    }

                    return ($aDateValue > $bDateValue) ? -1 : 1;
                }
            );

            return;
        }

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

    /**
     * @return Row[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    public function count(): int
    {
        return count($this->rows);
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

    public static function fromJson(string $json): self
    {
        $rows = array_map(
            fn(array $row) => Row::fromArray($row),
            json_decode($json, true)
        );
        return new self($rows);
    }
}