<?php

namespace App;

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
        $fieldName = $sorting->getSortField()->value;

        if ($sorting->getSortField()->getType() === SORT_NUMERIC) {
            usort(
                $this->rows,
                function (array $a, array $b) use ($fieldName) {
                    if ($a[$fieldName] == $b[$fieldName]) {
                        return 0;
                    }
                    return ($a[$fieldName] > $b[$fieldName]) ? -1 : 1;
                }
            );

            return;
        }

        usort(
            $this->rows,
            fn(array $a, array $b) => strcmp($a[$fieldName], $b[$fieldName])
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
            if ($row['approximateTime'] === '0 min') {
                unset($this->rows[$delta]);
                continue;
            }

            $key = $row['title'] . $row['platform'] . ($row['region'] ?? '');
            if (!in_array($key, $keys)) {
                $keys[] = $key;
                continue;
            }

            unset($this->rows[$delta]);
        }
    }

    public static function fromJson(string $json): self
    {
        return new self(json_decode($json, true));
    }
}