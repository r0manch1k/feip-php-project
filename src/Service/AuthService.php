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
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function saveUser(UserDto $user): User
    {
        if (null === $user->phoneNumber) {
            throw new InvalidArgumentException('Phone number cannot be null');
        }

        $newUser = new User(
            id: null,
            phoneNumber: $user->phoneNumber,
            roles: $user->roles,
            password: $user->password,
        );

        $errors = $this->validator->validate($newUser);

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
