<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SummerHouse;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SummerHouse>
 */
class SummerHouseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SummerHouse::class);
    }

    /**
     * @return SummerHouse[]
     */
    public function getMostExpensiveHouses(int $limit = 5): array
    {
        return $this->createQueryBuilder('h')
            ->orderBy('h.price', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return SummerHouse[]
     */
    public function getUnbookedHouses(DateTimeInterface $dateTime, int $limit = -1): array
    {
        $qb = $this->createQueryBuilder('h');

        $sub = $this->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from('App\Entity\Booking', 'b')
            ->where('b.house = h')
            ->andWhere(':dateTime BETWEEN b.startDate AND b.endDate');

        $qb->where(
            $qb->expr()->not(
                $qb->expr()->exists($sub->getDQL())
            )
        )
            ->setParameter('dateTime', $dateTime);

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
