<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SummerHouse;
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

    // /**
    //  * @return SummerHouse[]
    //  */
    // public getUnbookedHouses(int $limit = 5): array
    // {
    //     return $this->createQueryBuilder('h')

    // }
}
