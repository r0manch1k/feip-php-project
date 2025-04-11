<?php

namespace App\Service;

use App\Dto\SummerHouseDto;

class SummerHouseService
{
    private string $csvFilePath;

    public function __construct(string $csvFilePath, ?string $csvFilePathOverride = null)
    {
        if ($csvFilePathOverride !== null) {
            $this->csvFilePath = $csvFilePathOverride;
        } else {
            $this->csvFilePath = $csvFilePath;
        }
    }

    /**
     * @return int|false
     */
    private function getLastId(): int | false
    {
        $summerHouses = $this->getSummerHouses();

        if ($summerHouses === false) {
            return false;
        }

        /**
         * @var int $lastId
         */
        $lastId = 0;

        foreach ($summerHouses as $summerHouse) {
            if ($summerHouse->id > $lastId) {
                $lastId = $summerHouse->id;
            }
        }

        return $lastId;
    }

    /**
     * @param int $houseId
     * return bool
     */
    public function isHouseIdExists(int $houseId): bool
    {
        $summerHouses = $this->getSummerHouses();

        if ($summerHouses === false) {
            return false;
        }

        foreach ($summerHouses as $summerHouse) {
            if ($summerHouse->id === $houseId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return SummerHouseDto[]|false
     */
    public function getSummerHouses(): array | false
    {
        /**
         * @var SummerHouseDto[] $summerHouses
         */
        $summerHouses = [];

        try {
            $file = fopen($this->csvFilePath, 'r');
        } catch (\Exception $e) {
            return false;
        }

        if ($file === false) {
            return false;
        }

        while (($data = fgetcsv($file, escape: '\\')) !== false) {
            if ($data !== null) {
                $summerHouses[] = new SummerHouseDto(
                    id: (int)$data[0],
                    address: $data[1],
                    price: (int)$data[2],
                    bedrooms: (int)$data[3],
                    distanceFromSea: (int)$data[4],
                    hasShower: (bool)$data[5],
                    hasBathroom: (bool)$data[6]
                );
            }
        }
        fclose($file);

        return $summerHouses;
    }

    /**
     * @param SummerHouseDto[] $summerHouses
     * @param bool $rewrite
     * @return bool
     */
    public function saveSummerHouses(array $summerHouses, bool $rewrite = false): bool
    {
        /**
         * @var int $startId
         */
        $startId = -1;

        if ($rewrite === false) {
            $startId = $this->getLastId();

            if ($startId === false) {
                return false;
            }
        }

        try {
            $file = fopen($this->csvFilePath, $rewrite ? 'w' : 'a');
        } catch (\Exception $e) {
            return false;
        }

        if ($file === false) {
            return false;
        }

        foreach ($summerHouses as $summerHouse) {
            fputcsv($file, [
                ++$startId,
                $summerHouse->address,
                $summerHouse->price,
                $summerHouse->bedrooms,
                $summerHouse->distanceFromSea,
                $summerHouse->hasShower,
                $summerHouse->hasBathroom
            ], escape: '\\');
        }

        fclose($file);

        return true;
    }
}
