<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TelegramBotUserDto;
use App\Entity\TelegramBotUser;
use App\Exception\TelegramBotUserAlreadyExistsException;
use App\Exception\TelegramBotUserNoChangesDetectedException;
use App\Repository\TelegramBotUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TelegramBotUserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TelegramBotUserRepository $telegramBotUserRepository,
    ) {
    }

    /**
     * @return TelegramBotUserDto[]
     */
    public function getTelegramBotUsers(): array
    {
        /**
         * @var TelegramBotUser[] $users
         */
        $users = $this->telegramBotUserRepository->findAll();

        $users = array_map(
            fn (TelegramBotUser $user) => new TelegramBotUserDto(
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

    public function getTelegramBotUserByTelegramId(int $telegramId): TelegramBotUserDto
    {
        $user = $this->telegramBotUserRepository->findOneBy(['telegramId' => $telegramId]);

        if (!$user) {
            throw new RuntimeException('telegram user not found (id: ' . (string) $telegramId . ')');
        }

        return new TelegramBotUserDto(
            id: $user->getId(),
            telegramId: $user->getTelegramId() ?? throw new RuntimeException('telegram id cannot be null'),
            username: $user->getUsername() ?? throw new RuntimeException('username cannot be null'),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            phoneNumber: $user->getPhoneNumber(),
        );
    }

    public function saveTelegramBotUser(ValidatorInterface $validator, TelegramBotUserDto $userDto): void
    {
        $existingUser = $this->telegramBotUserRepository->findOneBy(['telegramId' => $userDto->telegramId]);

        if ($existingUser) {
            $id = (string) $existingUser->getId();
            throw new TelegramBotUserAlreadyExistsException('user already exists (id: ' . $id . ')');
        }

        $newTelegramBotUser = new TelegramBotUser(
            id: null,
            telegramId: $userDto->telegramId,
            username: $userDto->username,
            firstName: $userDto->firstName,
            lastName: $userDto->lastName,
            phoneNumber: $userDto->phoneNumber,
        );

        $errors = $validator->validate($newTelegramBotUser);

        if (count($errors) > 0) {
            throw new InvalidArgumentException('validation failed: ' . (string) $errors);
        }

        $this->entityManager->persist($newTelegramBotUser);

        $this->entityManager->flush();
    }

    public function changeTelegramBotUser(ValidatorInterface $validator, TelegramBotUserDto $userDto): void
    {
        $existingUser = $this->telegramBotUserRepository->findOneBy(['telegramId' => $userDto->telegramId]);

        if (!$existingUser) {
            throw new RuntimeException('user not found (id: ' . (string) $userDto->telegramId . ')');
        }

        if (
            $existingUser->getTelegramId() === $userDto->telegramId
            && $existingUser->getUsername() === $userDto->username
            && $existingUser->getFirstName() === $userDto->firstName
            && $existingUser->getLastName() === $userDto->lastName
            && $existingUser->getPhoneNumber() === $userDto->phoneNumber
        ) {
            throw new TelegramBotUserNoChangesDetectedException('no changes detected');
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
