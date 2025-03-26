<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Service\BookingService;
use App\Service\SummerHouseService;

use App\Dto\BookingDto;

class BookingServiceTest extends KernelTestCase
{
    public function testGetBookings(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $testCsvFile = '/tests/csv/bookings_1.csv';

        $bookingService = new BookingService($kernel, $testCsvFile);

        /**
         * @var BookingDto[] $bookings
         */
        $bookings = $bookingService->getBookings();

        $this->assertNotFalse($bookings);

        $this->assertIsArray($bookings);

        for ($i = 0; $i < count($bookings); $i++) {
            $this->assertInstanceOf(BookingDto::class, $bookings[$i]);
        }
    }

    public function testSaveBookings(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        /**
         * @var string $testCsvFile
         */
        $testCsvFile = '/tests/csv/bookings_2.csv';

        $bookingService = new BookingService($kernel, $testCsvFile);

        /**
         * @var SummerHouseService $summerHouseService_WA
         */
        $testSHCsvFile_WA = '/tests/csv/summerhouses_1.csv';

        /**
         * @var SummerHouseService $summerHouseService_OK
         */
        $testSHSCsvFile_OK = '/tests/csv/summerhouses_2.csv';

        $summerHouseService_WA = new SummerHouseService($kernel, $testSHCsvFile_WA);
        $summerHouseService_OK = new SummerHouseService($kernel, $testSHSCsvFile_OK);

        /**
         * @var BookingDto[] $newBookings
         */
        $newBookings = [
            new BookingDto(
                id: -1,
                phoneNumber: '123456789',
                houseId: 1,
                comment: 'test'
            ),
            new BookingDto(
                id: -1,
                phoneNumber: '987654321',
                houseId: 2,
                comment: 'test'
            ),
            new BookingDto(
                id: -1,
                phoneNumber: '123456789',
                houseId: 3,
                comment: 'test'
            ),
        ];

        $this->assertFalse($bookingService->saveBookings($summerHouseService_WA, $newBookings, true,));

        $this->assertNotFalse($bookingService->saveBookings($summerHouseService_OK, $newBookings, true,));

        /**
         * @var BookingDto[] $bookings
         */
        $bookings = $bookingService->getBookings();

        $this->assertNotFalse($bookings);

        $this->assertCount(count($newBookings), $bookings);
    }

    public function testUniqueIds(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $testCsvFile = '/tests/csv/bookings_2.csv';

        $bookingService = new BookingService($kernel, $testCsvFile);

        /**
         * @var SummerHouseService $summerHouseService_OK
         */
        $testSHSCsvFile_OK = '/tests/csv/summerhouses_2.csv';
        $testSHSCsvFile_OK = '/tests/csv/summerhouses_2.csv';

        $summerHouseService_OK = new SummerHouseService($kernel, $testSHSCsvFile_OK);

        /**
         * @var BookingDto[] $newBookings
         */
        $newBookings = [
            new BookingDto(
                id: -1,
                phoneNumber: '123456789',
                houseId: 1,
                comment: 'test'
            ),
            new BookingDto(
                id: -1,
                phoneNumber: '987654321',
                houseId: 2,
                comment: 'test'
            ),
            new BookingDto(
                id: -1,
                phoneNumber: '123456789',
                houseId: 3,
                comment: 'test'
            ),
        ];

        $bookingService->saveBookings($summerHouseService_OK, $newBookings);

        $this->assertNotFalse($bookingService->saveBookings($summerHouseService_OK, $newBookings));

        /**
         * @var BookingDto[] $bookings
         */
        $bookings = $bookingService->getBookings();

        $ids = [];

        for ($i = 0; $i < count($bookings); $i++) {
            $ids[] = $bookings[$i]->id;
        }

        $this->assertCount(count($ids), array_unique($ids));
    }
}
