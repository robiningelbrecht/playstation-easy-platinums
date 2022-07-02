<?php

namespace App\Result;

use App\Sort\SortDirection;
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
        if ($sorting->getSortField()->getType() === SORT_NUMERIC) {
            usort(
                $this->rows,
                function (Row $a, Row $b) use ($sorting) {
                    $sortField = $sorting->getSortField();
                    if ($a->getValueBySortField($sortField) === $b->getValueBySortField($sortField)) {
                        return 0;
                    }

                    if ($sorting->getSortDirection() === SortDirection::ASC) {
                        return ($a->getValueBySortField($sortField) < $b->getValueBySortField($sortField)) ? -1 : 1;
                    }

                    return ($a->getValueBySortField($sortField) > $b->getValueBySortField($sortField)) ? -1 : 1;
                }
            );

            return;
        }

        usort(
            $this->rows,
            function (Row $a, Row $b) use ($sorting) {
                $sortField = $sorting->getSortField();

                if ($sorting->getSortDirection() === SortDirection::ASC) {
                    return strcmp($a->getValueBySortField($sortField), $b->getValueBySortField($sortField));
                }
                return strcmp($b->getValueBySortField($sortField), $a->getValueBySortField($sortField));
            }
        );
    }

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