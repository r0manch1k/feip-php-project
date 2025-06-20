<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TelegramBotUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TelegramBotUserRepository::class)]
#[ORM\Table(name: 'telegram_users')]
// Remove the index because writing to db now is more frequent than reading
// #[ORM\Index(columns: ['telegram_id'], name: 'idx_telegram_id')]
class TelegramBotUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint', unique: true)]
    private ?int $telegramId = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 16, nullable: true, unique: true)]
    private ?string $phoneNumber = null;

    public function __construct(
        ?int $id,
        int $telegramId,
        string $username,
        ?string $firstName,
        ?string $lastName,
        ?string $phoneNumber,
    ) {
        $this->id = $id;
        $this->telegramId = $telegramId;
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phoneNumber = $phoneNumber;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTelegramId(): ?int
    {
        return $this->telegramId;
    }

    public function setTelegramId(int $telegramId): static
    {
        $this->telegramId = $telegramId;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
}
