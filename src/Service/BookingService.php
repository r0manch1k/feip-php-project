<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

use App\Dto\BookingDto;
use App\Kernel;
use App\Service\SummerHouseService;

class BookingService
{
    private KernelInterface $kernel;
    private string $csvFile;

    // use Symfony\Component\Filesystem\Path; ?
    public function __construct(KernelInterface $kernel, ?string $csvFileOverride = null)
    {
        $projectDir = $kernel->getProjectDir();
        $this->csvFile = $projectDir . ($csvFileOverride ?? '/csv/bookings.csv');
        $this->kernel = $kernel;
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
     * @return BookingDto[]|false
     */
    public function getBookings(): array | false
    {
        /**
         * @var BookingDto[] $bookings
         */
        $bookings = [];

        try {
            $file = fopen($this->csvFile, 'r');
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
            $file = fopen($this->csvFile, $rewrite ? 'w' : 'a');
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
