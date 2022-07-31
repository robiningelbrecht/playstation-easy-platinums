<?php

namespace App\Statistics;

use App\Result\ResultSet;
use App\Result\Row;

class PlatformRegionMatrix
{
    private function __construct(
        private readonly ResultSet $resultSet
    )
    {
    }

    public static function fromResultSet(ResultSet $resultSet): self
    {
        return new self($resultSet);
    }

    public function getPlatforms(): array
    {
        return array_unique(array_map(
            fn(Row $row) => $row->getPlatform(),
            $this->resultSet->getRows()
        ));
    }

    public function getRegions(): array
    {
        $regions = array_unique(array_map(
            fn(Row $row) => $row->getRegion(),
            $this->resultSet->getRows()
        ));

        uasort($regions, function (?string $a, ?string $b) {
            // Push empty region to back of list.
            if ($a === null) {
                $a = 'zzzz';
            }
            if ($b === null) {
                $b = 'zzzz';
            }
            return strcmp($a, $b);
        });

        return $regions;
    }

    public function getTotalForPlatformAndRegion(string $platform, ?string $region = null): int
    {
        return count(array_filter(
            $this->resultSet->getRows(),
            fn(Row $row) => $row->getPlatform() === $platform && $row->getRegion() === $region
        ));
    }

    public function getTotalForPlatform(string $platform): int
    {
        return count(array_filter(
            $this->resultSet->getRows(),
            fn(Row $row) => $row->getPlatform() === $platform
        ));
    }

    public function getTotalForRegion(?string $region = null): int
    {
        return count(array_filter(
            $this->resultSet->getRows(),
            fn(Row $row) => $row->getRegion() === $region
        ));
    }
}