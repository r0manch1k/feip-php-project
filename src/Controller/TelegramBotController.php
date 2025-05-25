<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\TelegramUserDto;
use App\Exception\TelegramUserAlreadyExistsException;
use App\Exception\TelegramUserNoChangesDetectedException;
use App\Service\TelegramBotResponseService;
use App\Service\TelegramBotService;
use App\Service\TelegramUserService;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TelegramBotController
{
    #[Route('/telegram/webhook', name: 'telegram_webhook', methods: ['POST'])]
    public function handle(
        Request $request,
        ValidatorInterface $validator,
        TelegramBotService $telegramBotService,
        TelegramUserService $telegramUserService,
        TelegramBotResponseService $telegramBotResponseService,
    ): Response {
        $data = json_decode($request->getContent(), true);

        /**
         * @var int $chatId
         */
        $chatId = $data['message']['chat']['id'];

        /**
         * @var string $text
         */
        $text = $data['message']['text'];

        /**
         * @var array $from
         */
        $from = $data['message']['from'];

        $bot = $telegramBotService->getBot();

        try {
            $this->saveUser($from, $validator, $telegramUserService);
        } catch (Exception $e) {
            $bot->sendMessage($chatId, $e->getMessage());

            return new Response('OK');
        }

        if ('/start' === $text) {
            $response = $telegramBotResponseService->getStartMessage();
            $bot->sendMessage(
                chatId: $chatId,
                text: $response->text,
                parseMode: $response->parseMode,
                replyMarkup: $response->replyMarkup
            );
        } else {
            $bot->sendMessage($chatId, "stop spamming man... {$text}");
        }

        return new Response('OK');
    }

    private function saveUser(array $from, ValidatorInterface $validator, TelegramUserService $telegramUserService): void
    {
        if (!isset($from['id']) || !isset($from['username'])) {
            throw new InvalidArgumentException('User ID is required');
        }

        $user = new TelegramUserDto(
            null,
            $from['id'],
            $from['username'],
            $from['first_name'] ?? null,
            $from['last_name'] ?? null,
            $from['phone_number'] ?? null,
        );

        try {
            $telegramUserService->saveTelegramUser($validator, $user);
        } catch (TelegramUserAlreadyExistsException $e) {
        }

        try {
            $telegramUserService->changeTelegramUser($validator, $user);
        } catch (TelegramUserNoChangesDetectedException $e) {
        }
    }

    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(
        TelegramBotResponseService $telegramBotResponseService,
    ): Response {
        $response = $telegramBotResponseService->getStartMessage();

        return new Response($response->text);
    }
}
