<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Override;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $admin = new User(
            id: null,
            phoneNumber: '+79990000000',
            roles: ['ROLE_ADMIN'],
            password: 'poE@mTqPY9k4L9fC',
        );

        $admin->setPassword($this->hasher->hashPassword($admin, (string) $admin->getPassword()));
        $manager->persist($admin);

        $user = new User(
            id: null,
            phoneNumber: '+79990000001',
            roles: ['ROLE_USER'],
            password: 'poE@mTqPY9k4L9fC',
        );

        $user->setPassword($this->hasher->hashPassword($user, (string) $user->getPassword()));
        $manager->persist($user);

        $manager->flush();
    }
}
