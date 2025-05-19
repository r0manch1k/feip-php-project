<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\BookingDto;
use App\Entity\Booking;
use App\Entity\SummerHouse;
use App\Entity\User;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BookingRepository $bookingRepository,
    ) {
    }

    /**
     * @return BookingDto[]
     */
    public function getBookings(): array
    {
        /**
         * @var Booking[] $bookings
         */
        $bookings = $this->bookingRepository->findAll();

        $bookings = array_map(
            fn (Booking $booking) => new BookingDto(
                id: $booking->getId(),
                user: $booking->getUser(),
                houseId: $booking->getHouse()->getId() ?? throw new LogicException('house cannot be null'),
                comment: $booking->getComment(),
                startDate: $booking->getStartDate(),
                endDate: $booking->getEndDate()
            ),
            $bookings
        );

        return $bookings;
    }

    public function saveBooking(ValidatorInterface $validator, BookingDto $booking): void
    {
        $house = $this->entityManager->getRepository(SummerHouse::class)->find($booking->houseId);

        if (!$house) {
            throw new EntityNotFoundException('house doesn\'t exist (id: ' . $booking->houseId . ')');
        }

        $newBooking = new Booking(
            id: null,
            user: $booking->user,
            house: $house,
            startDate: $booking->startDate,
            endDate: $booking->endDate,
            comment: $booking->comment
        );

        $errors = $validator->validate($newBooking);

        if (count($errors) > 0) {
            throw new InvalidArgumentException('validation failed: ' . (string) $errors);
        }

        if ($newBooking->getStartDate() > $newBooking->getEndDate()) {
            throw new InvalidArgumentException('start date is after end date');
        }

        $activeBookings = $this->bookingRepository->findActiveBookings($house, $booking->startDate, $booking->endDate);

        if (count($activeBookings) > 0) {
            throw new InvalidArgumentException('house is already booked (id: ' . (string) $house->getId() . ')');
        }

        $this->entityManager->persist($newBooking);

        $this->entityManager->flush();
    }

    public function changeBooking(ValidatorInterface $validator, BookingDto $booking): void
    {
        if (null === $booking->id) {
            throw new InvalidArgumentException('booking id is null');
        }

        $existingBooking = $this->bookingRepository->find($booking->id);

        if (!$existingBooking) {
            throw new EntityNotFoundException('booking doesn\'t exist (id: ' . $booking->id . ')');
        }

        if ($existingBooking->getUser() !== $booking->user) {
            throw new InvalidArgumentException('access denied');
        }

        $existingBooking->setComment($booking->comment);
        $existingBooking->setUser($booking->user);
        $existingBooking->setStartDate($booking->startDate);
        $existingBooking->setEndDate($booking->endDate);

        $house = $this->entityManager->getRepository(SummerHouse::class)->find($booking->houseId);

        if (!$house) {
            throw new EntityNotFoundException('house doesn\'t exist (id: ' . $booking->houseId . ')');
        }

        $existingBooking->setHouse($house);

        $errors = $validator->validate($existingBooking);

        if (count($errors) > 0) {
            throw new InvalidArgumentException('validation failed: ' . (string) $errors);
        }

        $this->entityManager->persist($existingBooking);

        $this->entityManager->flush();
    }

    public function deleteBooking(int $bookingId, User $user): void
    {
        $booking = $this->bookingRepository->find($bookingId);

        if (!$booking) {
            throw new EntityNotFoundException('booking doesn\'t exist (id: ' . $bookingId . ')');
        }

        if ($user !== $booking->getUser()) {
            throw new InvalidArgumentException('access denied');
        }

        $this->entityManager->remove($booking);

        $this->entityManager->flush();
    }
}
