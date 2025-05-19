<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function saveUser(ValidatorInterface $validator, UserDto $user): User
    {
        $newUser = new User(
            id: null,
            phoneNumber: $user->phoneNumber,
            roles: $user->roles,
            password: $user->password,
        );

        $errors = $validator->validate($newUser);

        if (count($errors) > 0) {
            throw new InvalidArgumentException('validation failed: ' . (string) $errors);
        }

        $hashedPassword = $this->passwordHasher->hashPassword($newUser, $user->password);
        $newUser->setPassword($hashedPassword);

        $this->entityManager->persist($newUser);

        $this->entityManager->flush();

        return $newUser;
    }
}
