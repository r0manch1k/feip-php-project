<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TelegramUserDto;
use App\Entity\TelegramUser;
use App\Exception\TelegramUserAlreadyExistsException;
use App\Exception\TelegramUserNoChangesDetectedException;
use App\Repository\TelegramUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TelegramUserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TelegramUserRepository $telegramUserRepository,
    ) {
    }

    /**
     * @return TelegramUserDto[]
     */
    public function getTelegramUsers(): array
    {
        /**
         * @var TelegramUser[] $users
         */
        $users = $this->telegramUserRepository->findAll();

        $users = array_map(
            fn (TelegramUser $user) => new TelegramUserDto(
                id: $user->getId(),
                telegramId: $user->getTelegramId() ?? throw new RuntimeException('telegram id cannot be null'),
                username: $user->getUsername() ?? throw new RuntimeException('username cannot be null'),
                firstName: $user->getFirstName(),
                lastName: $user->getLastName(),
                phoneNumber: $user->getPhoneNumber(),
            ),
            $users,
        );

        return $users;
    }

    public function saveTelegramUser(ValidatorInterface $validator, TelegramUserDto $userDto): void
    {
        $existingUser = $this->telegramUserRepository->findOneBy(['telegramId' => $userDto->telegramId]);

        if ($existingUser) {
            throw new TelegramUserAlreadyExistsException('user already exists (id: ' . (string) $existingUser->getId() . ')');
        }

        $newTelegramUser = new TelegramUser(
            id: null,
            telegramId: $userDto->telegramId,
            username: $userDto->username,
            firstName: $userDto->firstName,
            lastName: $userDto->lastName,
            phoneNumber: $userDto->phoneNumber,
        );

        $errors = $validator->validate($newTelegramUser);

        if (count($errors) > 0) {
            throw new InvalidArgumentException('validation failed: ' . (string) $errors);
        }

        $this->entityManager->persist($newTelegramUser);

        $this->entityManager->flush();
    }

    public function changeTelegramUser(ValidatorInterface $validator, TelegramUserDto $userDto): void
    {
        $existingUser = $this->telegramUserRepository->findOneBy(['telegramId' => $userDto->telegramId]);

        if (!$existingUser) {
            throw new RuntimeException('user not found (id: ' . (string) $userDto->telegramId . ')');
        }

        if ($existingUser->getTelegramId() === $userDto->telegramId
            && $existingUser->getUsername() === $userDto->username
            && $existingUser->getFirstName() === $userDto->firstName
            && $existingUser->getLastName() === $userDto->lastName
            && $existingUser->getPhoneNumber() === $userDto->phoneNumber) {
            throw new TelegramUserNoChangesDetectedException('no changes detected');
        }

        if ($existingUser->getTelegramId() !== $userDto->telegramId) {
            $existingUser->setTelegramId($userDto->telegramId);
        }
        if ($existingUser->getUsername() !== $userDto->username) {
            $existingUser->setUsername($userDto->username);
        }
        if ($existingUser->getFirstName() !== $userDto->firstName) {
            $existingUser->setFirstName($userDto->firstName);
        }
        if ($existingUser->getLastName() !== $userDto->lastName) {
            $existingUser->setLastName($userDto->lastName);
        }
        if ($existingUser->getPhoneNumber() !== $userDto->phoneNumber) {
            $existingUser->setPhoneNumber($userDto->phoneNumber);
        }

        $errors = $validator->validate($existingUser);

        if (count($errors) > 0) {
            throw new InvalidArgumentException('validation failed: ' . (string) $errors);
        }

        $this->entityManager->flush();
    }
}
