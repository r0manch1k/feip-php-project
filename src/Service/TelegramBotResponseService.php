<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TelegramResponseDto;
use App\Dto\TelegramUserDto;
use App\Repository\SummerHouseRepository;
use App\Repository\TelegramUserRepository;
use RuntimeException;
use Twig\Environment;

class TelegramBotResponseService
{
    public function __construct(
        private readonly TelegramUserRepository $telegramUserRepository,
        private readonly TelegramBotService $telegramBotService,
        private readonly SummerHouseRepository $summerHouseRepository,
        private readonly Environment $twig,
    ) {
    }

    public function getStartMessage(): TelegramResponseDto
    {
        $houses = $this->summerHouseRepository->getMostExpensiveHouses(3);

        $replyMarkup = json_encode([
            'inline_keyboard' => [
                [
                    ['text' => 'House 1', 'callback_data' => 'house_1'],
                    ['text' => 'House 2', 'callback_data' => 'house_2'],
                    ['text' => 'House 3', 'callback_data' => 'house_3'],
                ],
            ],
        ]);

        if (false === $replyMarkup) {
            throw new RuntimeException('failed to encode reply markup');
        }

        $response = new TelegramResponseDto(
            text: $this->twig->render('bot/start_message.txt.twig', [
                'houses' => $houses,
            ]),
            replyMarkup: null,
            parseMode: 'MarkdownV2',
            inlineKeyboard: null,
        );

        return $response;
    }

    public function sendMessage(TelegramUserDto $user, string $message): void
    {
        $this->telegramBotService->getBot()->sendMessage($user->telegramId, $message);
    }
}
