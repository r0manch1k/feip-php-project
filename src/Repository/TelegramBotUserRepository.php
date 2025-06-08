<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TelegramBotUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TelegramBotUser>
 */
class TelegramBotUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramBotUser::class);
    }
}
