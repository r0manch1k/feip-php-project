<?php

declare(strict_types=1);

namespace App\Dto;

readonly class TelegramBotUserDto
{
    public function __construct(
        public ?int $id,
        public int $telegramId,
        public string $username,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $phoneNumber,
    ) {
    }
}
