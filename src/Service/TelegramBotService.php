<?php

declare(strict_types=1);

namespace App\Service;

use TelegramBot\Api\BotApi;

class TelegramBotService
{
    private BotApi $bot;

    public function __construct(string $token)
    {
        $this->bot = new BotApi($token);
    }

    public function getBot(): BotApi
    {
        return $this->bot;
    }
}
