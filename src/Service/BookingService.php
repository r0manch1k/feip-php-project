<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\BookingDto;
use App\Dto\SummerHouseDto;
use App\Dto\TelegramBotUserDto;
use App\Dto\UserDto;
use App\Entity\Booking;
use App\Entity\SummerHouse;
use App\Entity\TelegramBotUser;
use App\Entity\User;
use App\Exception\HouseAlreadyBookedException;
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
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @return BookingDto[]
     */
    public function getBookings(UserDto|TelegramBotUserDto|null $user = null): array
    {
        if ($user) {
            if ($user instanceof UserDto) {
                /**
                 * @var User $userEntity
                 */
                $userEntity = $this->entityManager->getRepository(User::class)->find($user->id);

                $bookings = $this->bookingRepository->findBookingsByUserSorted($userEntity);
            } else {
                /**
                 * @var TelegramBotUserDto $telegraBotUserEntity
                 */
                $telegramBotUserEntity = $this->entityManager->getRepository(TelegramBotUser::class)->find($user->id);

                if (!$telegramBotUserEntity) {
                    throw new EntityNotFoundException('telegram bot user not found');
                }

                $bookings = $this->bookingRepository->findBookingsByTelegramBotUserSorted($telegramBotUserEntity);
            }
        } else {
            /**
             * @var Booking[] $bookings
             */
            $bookings = $this->bookingRepository->findAll();
        }

        $bookings = array_map(
            fn (Booking $booking) => new BookingDto(
                id: $booking->getId(),
                user: $booking->getUser(),
                telegramBotUser: $booking->getTelegramBotUser(),
                houseId: $booking->getHouse()->getId() ?? throw new LogicException('house cannot be null'),
                house: new SummerHouseDto(
                    id: $booking->getHouse()->getId(),
                    address: $booking->getHouse()->getAddress(),
                    price: $booking->getHouse()->getPrice(),
                    bedrooms: $booking->getHouse()->getBedrooms(),
                    distanceFromSea: $booking->getHouse()->getDistanceFromSea(),
                    hasShower: $booking->getHouse()->getHasShower(),
                    hasBathroom: $booking->getHouse()->getHasBathroom(),
                ),
                comment: $booking->getComment(),
                startDate: $booking->getStartDate(),
                endDate: $booking->getEndDate(),
                totalPrice: $booking->getTotalPrice(),
                isActive: $booking->getIsActive(),
            ),
            $bookings
        );

        return $bookings;
    }

    public function getBookingById(int $bookingId): BookingDto
    {
        $booking = $this->bookingRepository->find($bookingId);

        if (!$booking) {
            throw new EntityNotFoundException('booking not found (id: ' . $bookingId . ')');
        }

        return new BookingDto(
            id: $booking->getId(),
            user: $booking->getUser(),
            telegramBotUser: $booking->getTelegramBotUser(),
            houseId: $booking->getHouse()->getId() ?? throw new LogicException('house cannot be null'),
            house: new SummerHouseDto(
                id: $booking->getHouse()->getId(),
                address: $booking->getHouse()->getAddress(),
                price: $booking->getHouse()->getPrice(),
                bedrooms: $booking->getHouse()->getBedrooms(),
                distanceFromSea: $booking->getHouse()->getDistanceFromSea(),
                hasShower: $booking->getHouse()->getHasShower(),
                hasBathroom: $booking->getHouse()->getHasBathroom(),
            ),
            comment: $booking->getComment(),
            startDate: $booking->getStartDate(),
            endDate: $booking->getEndDate(),
            totalPrice: $booking->getTotalPrice(),
            isActive: $booking->getIsActive()
        );
    }

    public function saveBooking(BookingDto $booking): void
    {
        $house = $this->entityManager->getRepository(SummerHouse::class)->find($booking->houseId);

        if (!$house) {
            throw new EntityNotFoundException('house doesn\'t exist (id: ' . $booking->houseId . ')');
        }

        $newBooking = new Booking(
            id: null,
            user: $booking->user,
            telegramBotUser: $booking->telegramBotUser,
            house: $house,
            startDate: $booking->startDate,
            endDate: $booking->endDate,
            comment: $booking->comment
        );

        $errors = $this->validator->validate($newBooking);

        if (count($errors) > 0) {
            throw new InvalidArgumentException('validation failed: ' . (string) $errors);
        }

        if ($newBooking->getStartDate() > $newBooking->getEndDate()) {
            throw new InvalidArgumentException('start date is after end date');
        }

        $activeBookings = $this->bookingRepository->findActiveBookings($house, $booking->startDate, $booking->endDate);

        if (count($activeBookings) > 0) {
            throw new HouseAlreadyBookedException('house is already booked (id: ' . (string) $house->getId() . ')');
        }

        $this->entityManager->persist($newBooking);

        $this->entityManager->flush();
    }

    public function changeBooking(BookingDto $booking): void
    {
        if (null === $booking->id) {
            throw new InvalidArgumentException('booking id is null');
        }

        $existingBooking = $this->bookingRepository->find($booking->id);

        if (!$existingBooking) {
            throw new EntityNotFoundException('booking doesn\'t exist (id: ' . $booking->id . ')');
        }

        if ($existingBooking->getUser() !== $booking->user
            && $existingBooking->getTelegramBotUser() !== $booking->telegramBotUser) {
            throw new InvalidArgumentException('access denied');
        }

        $existingBooking->setComment($booking->comment);
        $existingBooking->setUser($booking->user);
        $existingBooking->setTelegramBotUser($booking->telegramBotUser);
        $existingBooking->setStartDate($booking->startDate);
        $existingBooking->setEndDate($booking->endDate);

        $house = $this->entityManager->getRepository(SummerHouse::class)->find($booking->houseId);

        if (!$house) {
            throw new EntityNotFoundException('house doesn\'t exist (id: ' . $booking->houseId . ')');
        }

        $existingBooking->setHouse($house);

        $errors = $this->validator->validate($existingBooking);

        if (count($errors) > 0) {
            throw new InvalidArgumentException('validation failed: ' . (string) $errors);
        }

        $this->entityManager->persist($existingBooking);

        $this->entityManager->flush();
    }

    public function deleteBooking(int $bookingId, User|TelegramBotUser|null $user = null): void
    {
        $booking = $this->bookingRepository->find($bookingId);

        if (!$booking) {
            throw new EntityNotFoundException('booking doesn\'t exist (id: ' . $bookingId . ')');
        }

        if ($user && $user !== $booking->getUser() && $user !== $booking->getTelegramBotUser()) {
            throw new InvalidArgumentException('access denied');
        }
        $this->entityManager->remove($booking);

        $this->entityManager->flush();
    }
}
