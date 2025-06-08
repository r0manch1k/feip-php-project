<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\BookingStep;
use DateTimeInterface;

class TelegramBotBoookingProgressDto
{
    public function __construct(
        public BookingStep $step,
        public ?SummerHouseDto $houseDto = null,
        public ?TelegramBotUserDto $telegramBotUserDto = null,
        public ?DateTimeInterface $startDate = null,
        public ?DateTimeInterface $endDate = null,
        public ?float $totalPrice = null,
        public ?string $comment = null,
    ) {
    }
}
