<?php

namespace App\Service;

use App\Dto\BookingDto;
use App\Service\SummerHouseService;

class BookingService
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
        $bookings = $this->getBookings();

        if ($bookings === false) {
            return false;
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
        $bookings = $this->getBookings();

        if ($bookings === false) {
            return false;
        }

        foreach ($bookings as $booking) {
            if ($booking->id === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return BookingDto[]|false
     */
    public function getBookings(): array | false
    {
        /**
         * @var BookingDto[] $bookings
         */
        $bookings = [];

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
     * @return bool
     */
    public function saveBookings(SummerHouseService $summerHouseService, array $bookings, bool $rewrite = false): bool
    {
        for ($i = 0; $i < count($bookings); $i++) {
            if (!$summerHouseService->isHouseIdExists($bookings[$i]->houseId)) {
                return false;
            }
        }

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

        foreach ($bookings as $booking) {
            fputcsv($file, [
                ++$startId,
                $booking->phoneNumber,
                $booking->houseId,
                $booking->comment
            ], escape: '\\');
        }

        fclose($file);

        return true;
    }
}
