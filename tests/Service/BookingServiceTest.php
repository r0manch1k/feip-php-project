<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Dto\BookingDto;
use App\Entity\User;
use App\Service\BookingService;
use DateTime;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookingServiceTest extends KernelTestCase
{
    public function testGetBookings(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $container = static::getContainer();

        $container->get('doctrine')->getManager();

        $bookingService = $container->get(BookingService::class);

        try {
            $bookings = $bookingService->getBookings();
            $this->assertNotEmpty($bookings);
            $this->assertInstanceOf(BookingDto::class, $bookings[0]);
        } catch (Exception $e) {
            $this->fail('failed to get bookings: ' . $e->getMessage());
        }
    }

    public function testSaveBooking(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $container = static::getContainer();

        $container->get('doctrine')->getManager();

        $bookingService = $container->get(BookingService::class);

        $oldBookings = $bookingService->getBookings();

        $userRepository = $container->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['phoneNumber' => '+79990000001']);

        if (null === $user) {
            $this->fail('user not found');
        }

        try {
            $bookingDto = new BookingDto(
                id: null,
                user: $user,
                houseId: 10,
                comment: 'A happy house',
                startDate: new DateTime('2027-10-01'),
                endDate: new DateTime('2028-10-10')
            );

            $bookingService->saveBooking($container->get('validator'), $bookingDto);
        } catch (Exception $e) {
            $this->fail('failed to save booking: ' . $e->getMessage());
        }

        $newBookings = $bookingService->getBookings();

        $this->assertCount(count($oldBookings) + 1, $newBookings);
    }

    public function testSaveBookingWithInvalidDate(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $container = static::getContainer();

        $container->get('doctrine')->getManager();

        $bookingService = $container->get(BookingService::class);

        $userRepository = $container->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['phoneNumber' => '+79990000001']);

        if (null === $user) {
            $this->fail('user not found');
        }

        $bookingDto = new BookingDto(
            id: null,
            user: $user,
            houseId: 1,
            comment: 'A happy house',
            startDate: new DateTime('2020-10-01'),
            endDate: new DateTime('2030-10-10')
        );

        $this->expectException(InvalidArgumentException::class);

        $bookingService->saveBooking($container->get('validator'), $bookingDto);
    }
}
