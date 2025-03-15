<?php

declare(strict_types=1);

namespace App\Dto;

readonly class BookingDto
{
    public function __construct(
        public int $id,
        public string $phoneNumber,
        public int $houseId,
        public ?string $comment,
    ) {}
}
