<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\SummerHouse;
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
    public function findActiveBookings(SummerHouse $house, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
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
}
