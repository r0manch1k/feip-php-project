<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\TelegramBotUser;
use App\Entity\User;
use DateTimeInterface;

// TODO: Make users dto fields in BookingDto instead of entities.
readonly class BookingDto
{
    public function __construct(
        public int $houseId,
        public DateTimeInterface $startDate,
        public DateTimeInterface $endDate,
        public ?int $id = null,
        public ?User $user = null,
        public ?TelegramBotUser $telegramBotUser = null,
        public ?SummerHouseDto $house = null,
        public ?string $comment = null,
        public ?float $totalPrice = null,
        public ?bool $isActive = null,
    ) {
    }

    // TODO: Use serializer instead of this method
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'phoneNumber' => $this->user?->getPhoneNumber(),
            ],
            'TelegramBotUser' => [
                'telegramId' => $this->telegramBotUser?->getId(),
                'username' => $this->telegramBotUser?->getUsername(),
                'firstName' => $this->telegramBotUser?->getFirstName(),
                'lastName' => $this->telegramBotUser?->getLastName(),
            ],
            'houseId' => $this->houseId,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'comment' => $this->comment,
        ];
    }
}
