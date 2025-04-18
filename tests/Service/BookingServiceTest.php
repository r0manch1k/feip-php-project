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

        $bookingService = new BookingService($kernel->getProjectDir(), $testCsvFile);

        try {
            /**
             * @var BookingDto[] $bookings
             */
            $bookings = $bookingService->getBookings();
        } catch (\Exception $e) {
            $this->fail('Failed to get bookings: ' . $e->getMessage());
        }

        $this->assertIsArray($bookings);

        for ($i = 0; $i < count($bookings); $i++) {
            $this->assertInstanceOf(BookingDto::class, $bookings[$i]);
        }
    }

    public function testSaveBookings(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());


        $testCsvFile = '/tests/csv/bookings_2.csv';

        $bookingService = new BookingService($kernel->getProjectDir(), $testCsvFile);


        $testSHCsvFile_WA = $kernel->getProjectDir() . '/tests/csv/summerhouses_1.csv';

        $testSHSCsvFile_OK = $kernel->getProjectDir() . '/tests/csv/summerhouses_2.csv';

        /**
         * @var SummerHouseService $summerHouseService_WA
         */
        $summerHouseService_WA = new SummerHouseService($kernel->getProjectDir(), $testSHCsvFile_WA);

        /**
         * @var SummerHouseService $summerHouseService_OK
         */
        $summerHouseService_OK = new SummerHouseService($kernel->getProjectDir(), $testSHSCsvFile_OK);

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

        $this->expectException(\Exception::class);
        $bookingService->saveBookings($summerHouseService_WA, $newBookings, true,);

        try {
            $bookingService->saveBookings($summerHouseService_OK, $newBookings, true,);
        } catch (\Exception $e) {
            $this->fail('Failed to save bookings: ' . $e->getMessage());
        }

        try {
            /**
             * @var BookingDto[] $bookings
             */
            $bookings = $bookingService->getBookings();
        } catch (\Exception $e) {
            $this->fail('Failed to get bookings: ' . $e->getMessage());
        }

        $this->assertCount(count($newBookings), $bookings);
    }

    public function testUniqueIds(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $testCsvFile = '/tests/csv/bookings_2.csv';

        $bookingService = new BookingService($kernel->getProjectDir(), $testCsvFile);

        $testSHSCsvFile_OK = '/tests/csv/summerhouses_2.csv';

        /**
         * @var SummerHouseService $summerHouseService_OK
         */
        $summerHouseService_OK = new SummerHouseService($kernel->getProjectDir(), $testSHSCsvFile_OK);

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


        try {
            $bookingService->saveBookings($summerHouseService_OK, $newBookings);
        } catch (\Exception $e) {
            $this->fail('Failed to save bookings: ' . $e->getMessage());
        }

        try {
            /**
             * @var BookingDto[] $bookings
             */
            $bookings = $bookingService->getBookings();
        } catch (\Exception $e) {
            $this->fail('Failed to get bookings: ' . $e->getMessage());
        }

        $ids = [];

        for ($i = 0; $i < count($bookings); $i++) {
            $ids[] = $bookings[$i]->id;
        }

        $this->assertCount(count($ids), array_unique($ids));
    }
}
