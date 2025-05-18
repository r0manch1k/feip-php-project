<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;
use DateTimeInterface;

readonly class BookingDto
{
    public function __construct(
        public ?int $id,
        public User $user,
        public int $houseId,
        public DateTimeInterface $startDate,
        public DateTimeInterface $endDate,
        public ?string $comment,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'phoneNumber' => $this->user->getPhoneNumber(),
            ],
            'houseId' => $this->houseId,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'comment' => $this->comment,
        ];
    }
}
