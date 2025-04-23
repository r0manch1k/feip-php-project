<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Service\BookingService;
use App\Dto\BookingDto;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\ORM\EntityManagerInterface;

class BookingServiceTest extends KernelTestCase
{
    public function testGetBookings(): void
    {
        /**
         * @var KernelInterface $kernel
         */
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        /**
         * @var Container $container
         */
        $container = static::getContainer();

        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get('doctrine')->getManager();

        /**
         * @var BookingService $bookingService
         */
        $bookingService = $container->get(BookingService::class);

        try {
            $bookings = $bookingService->getBookings();
            $this->assertIsArray($bookings);
            $this->assertNotEmpty($bookings);
            $this->assertInstanceOf(BookingDto::class, $bookings[0]);
        } catch (\Exception $e) {
            $this->fail('failed to get bookings: ' . $e->getMessage());
        }
    }

    public function testSaveBooking(): void
    {
        /**
         * @var KernelInterface $kernel
         */
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        /**
         * @var Container $container
         */
        $container = static::getContainer();

        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get('doctrine')->getManager();

        /**
         * @var BookingService $bookingService
         */
        $bookingService = $container->get(BookingService::class);

        /**
         * @var BookingDto[] $oldBookings
         */
        $oldBookings = $bookingService->getBookings();

        try {
            $bookingDto = new BookingDto(
                id: null,
                phoneNumber: '+12223334455',
                houseId: 10,
                comment: 'a happy house',
                startDate: new \DateTime('2027-10-01'),
                endDate: new \DateTime('2028-10-10')
            );
            $bookingService->saveBooking($container->get('validator'), $bookingDto);
        } catch (\Exception $e) {
            $this->fail('failed to save booking: ' . $e->getMessage());
        }

        /**
         * @var BookingDto[] $newBookings
         */
        $newBookings = $bookingService->getBookings();

        $this->assertCount(count($oldBookings) + 1, $newBookings);
    }

    public function testSaveBookingWithInvalidDate(): void
    {
        /**
         * @var KernelInterface $kernel
         */
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        /**
         * @var Container $container
         */
        $container = static::getContainer();

        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get('doctrine')->getManager();

        /**
         * @var BookingService $bookingService
         */
        $bookingService = $container->get(BookingService::class);

        $bookingDto = new BookingDto(
            id: null,
            phoneNumber: '+12223334455',
            houseId: 1,
            comment: '(^_^)',
            startDate: new \DateTime('2020-10-01'),
            endDate: new \DateTime('2030-10-10')
        );

        $this->expectException(\InvalidArgumentException::class);

        $bookingService->saveBooking($container->get('validator'), $bookingDto);
    }
}
