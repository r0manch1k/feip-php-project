<?php

namespace App\Service;

use App\Dto\BookingDto;
use App\Service\SummerHouseService;

class BookingService
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
            $bookings = $this->getBookings();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get bookings: ' . $e->getMessage());
        }


        $lastId = 0;

        foreach ($bookings as $booking) {
            if ($booking->id > $lastId) {
                $lastId = $booking->id;
            }
        }

        return $lastId;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function isIdExists(int $id): bool
    {
        try {
            $bookings = $this->getBookings();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get bookings: ' . $e->getMessage());
        }

        foreach ($bookings as $booking) {
            if ($booking->id === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return BookingDto[]
     */
    public function getBookings(): array
    {
        /**
         * @var BookingDto[] $bookings
         */
        $bookings = [];

        $file = fopen($this->csvFilePath, 'r');

        if ($file === false) {
            throw new \RuntimeException('Failed to open file: ' . $this->csvFilePath);
        }

        while (($data = fgetcsv($file, escape: '\\')) !== false) {
            if ($data !== null) {
                $bookings[] = new BookingDto(
                    (int)$data[0],
                    $data[1],
                    (int)$data[2],
                    $data[3]
                );
            }
        }
        fclose($file);

        return $bookings;
    }

    /**
     * @param BookingDto[] $bookings
     * @param bool $rewrite
     * @return void
     */
    public function saveBookings(SummerHouseService $summerHouseService, array $bookings, bool $rewrite = false): void
    {
        for ($i = 0; $i < count($bookings); $i++) {
            if (!$summerHouseService->isHouseIdExists($bookings[$i]->houseId)) {
                throw new \Exception('House ID ' . $bookings[$i]->houseId . ' does not exist.');
            }
        }

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

        foreach ($bookings as $booking) {
            fputcsv($file, [
                ++$startId,
                $booking->phoneNumber,
                $booking->houseId,
                $booking->comment
            ], escape: '\\');
        }

        fclose($file);
    }
}
