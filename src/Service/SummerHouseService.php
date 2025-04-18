<?php

namespace App\Service;

use App\Dto\SummerHouseDto;

class SummerHouseService
{
    /**
     * @var string
     */
    private string $csvFilePath;

    public function __construct(string $projectDir, string $csvFilePath)
    {
        $this->csvFilePath = $projectDir . $csvFilePath;
    }

    /**
     * @return int
     */
    private function getLastId(): int
    {
        try {
            $summerHouses = $this->getSummerHouses();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get summer houses: ' . $e->getMessage());
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
        try {
            $summerHouses = $this->getSummerHouses();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get summer houses: ' . $e->getMessage());
        }

        foreach ($summerHouses as $summerHouse) {
            if ($summerHouse->id === $houseId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return SummerHouseDto[]
     */
    public function getSummerHouses(): array
    {
        /**
         * @var SummerHouseDto[] $summerHouses
         */
        $summerHouses = [];

        $file = fopen($this->csvFilePath, 'r');

        if ($file === false) {
            throw new \RuntimeException('Failed to open file: ' . $this->csvFilePath);
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
     * @return void
     */
    public function saveSummerHouses(array $summerHouses, bool $rewrite = false): void
    {
        /**
         * @var int $startId
         */
        $startId = -1;

        if ($rewrite === false) {
            try {
                $startId = $this->getLastId();
            } catch (\Exception $e) {
                throw new \Exception('Failed to get last ID: ' . $e->getMessage());
            }
        }

        $file = fopen($this->csvFilePath, $rewrite ? 'w' : 'a');

        if ($file === false) {
            throw new \RuntimeException('Failed to open file: ' . $this->csvFilePath);
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
    }
}
