<?php

declare(strict_types=1);

namespace App\Dto;

use TelegramBot\Api\Types\ForceReply;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class TelegramBotResponseDto
{
    public function __construct(
        public string $text,
        public ForceReply|InlineKeyboardMarkup|ReplyKeyboardMarkup|null $replyMarkup = null,
        public ?string $parseMode = null,
    ) {
    }
}
