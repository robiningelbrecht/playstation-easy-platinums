<?php

namespace App;

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

    public function sort(Sorting $sorting): void{
        krsort($this->rows, SORT_NUMERIC);
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
            if($row['approximateTime'] === '0 min'){
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