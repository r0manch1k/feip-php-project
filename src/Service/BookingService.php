<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

use App\Dto\BookingDto;

class BookingService
{
    private string $csvFile;

    public function __construct(KernelInterface $kernel)
    {
        $projectDir = $kernel->getProjectDir();
        $this->csvFile = $projectDir . '/csv/bookings.csv';
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

        while (($data = fgetcsv($file)) !== false) {
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
    public function saveBookings(array $bookings, bool $rewrite = false): bool
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

        foreach ($bookings as $booking) {
            fputcsv($file, [
                ++$startId,
                $booking->phoneNumber,
                $booking->houseId,
                $booking->comment
            ]);
        }

        fclose($file);

        return true;
    }
}
