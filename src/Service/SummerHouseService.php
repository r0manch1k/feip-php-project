<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

use App\Dto\SummerHouseDto;

class SummerHouseService
{
    private string $csvFile;

    public function __construct(KernelInterface $kernel)
    {
        $projectDir = $kernel->getProjectDir();
        $this->csvFile = $projectDir . '/csv/summerhouses.csv';
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
     * @return SummerHouseDto[]|false
     */
    public function getSummerHouses(): array | false
    {
        /**
         * @var SummerHouseDto[] $summerHouses
         */
        $summerHouses = [];

        try {
            $file = fopen($this->csvFile, 'r');
        } catch (\Exception $e) {
            return false;
        }

        if ($file === false) {
            return false;
        }

        while (($data = fgetcsv($file)) !== false) {
            if ($data !== null) {
                $summerHouses[] = new SummerHouseDto(
                    (int)$data[0],
                    $data[1],
                    (int)$data[2],
                    (int)$data[3],
                    (int)$data[4],
                    (bool)$data[5],
                    (bool)$data[6]
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
            $file = fopen($this->csvFile, $rewrite ? 'w' : 'a');
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
            ]);
        }

        fclose($file);

        return true;
    }
}
