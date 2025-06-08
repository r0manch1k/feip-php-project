<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\SummerHouse;
use App\Entity\TelegramBotUser;
use App\Entity\User;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    /**
     * @return Booking[]
     */
    public function findActiveBookings(
        SummerHouse $house,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
    ): array {
        return $this->createQueryBuilder('b')
            ->andWhere('b.house = :house')
            ->andWhere('
                (b.startDate <= :startDate AND b.endDate >= :startDate) OR 
                (b.startDate <= :endDate AND b.endDate >= :endDate) OR 
                (:startDate <= b.startDate AND :endDate >= b.endDate) OR 
                (:startDate >= b.startDate AND :endDate <= b.endDate)
            ')
            ->setParameter('house', $house)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Booking[]
     */
    public function findBookingsByUserSorted(User $user): array
    {
        $qb = $this->createQueryBuilder('b')
        ->where('b.user = :user')
        ->setParameter('user', $user)
        ->orderBy(
            'CASE WHEN b.endDate >= :now THEN 0 ELSE 1 END',
            'ASC'
        )
        ->addOrderBy('b.startDate', 'DESC')
        ->setParameter('now', new DateTimeImmutable());

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Booking[]
     */
    public function findBookingsByTelegramBotUserSorted(TelegramBotUser $telegramBotUser): array
    {
        $qb = $this->createQueryBuilder('b')
        ->where('b.telegramBotUser = :telegramBotUser')
        ->setParameter('telegramBotUser', $telegramBotUser)
        ->orderBy(
            'CASE WHEN b.endDate >= :now THEN 0 ELSE 1 END',
            'ASC'
        )
        ->addOrderBy('b.startDate', 'DESC')
        ->setParameter('now', new DateTimeImmutable());

        return $qb->getQuery()->getResult();
    }
}
