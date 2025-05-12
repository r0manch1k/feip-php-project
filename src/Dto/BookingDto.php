<?php

declare(strict_types=1);

namespace App\Dto;

use DateTimeInterface;

readonly class BookingDto
{
    public function __construct(
        public ?int $id,
        public string $phoneNumber,
        public int $houseId,
        public DateTimeInterface $startDate,
        public DateTimeInterface $endDate,
        public ?string $comment,
    ) {
    }
}
