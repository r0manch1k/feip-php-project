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
    public function __construct(
        private readonly TelegramBotService $telegramBotService,
        private readonly TelegramUserService $telegramUserService,
        private readonly ValidatorInterface $validator,
        private readonly TelegramBotResponseService $telegramBotResponseService,
    ) {
    }

    #[Route('/telegram/webhook', name: 'telegram_webhook', methods: ['POST'])]
    public function handle(Request $request): Response
    {
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

        $bot = $this->telegramBotService->getBot();

        try {
            $this->saveUser($from);
        } catch (Exception $e) {
            $bot->sendMessage($chatId, $e->getMessage());

            return new Response('OK');
        }

        if ('/start' === $text) {
            try {
                $response = $this->telegramBotResponseService->getStartMessage();
                $bot->sendMessage(
                    chatId: $chatId,
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $bot->sendMessage($chatId, $e->getMessage());

                return new Response('OK');
            }

        } else {
            $bot->sendMessage($chatId, "stop spamming man... {$text}");
        }

        return new Response('OK');
    }

    private function saveUser(array $from): void
    {
        if (!isset($from['id']) || !isset($from['username'])) {
            throw new InvalidArgumentException('User ID is required');
        }

        $user = new TelegramUserDto(
            id: null,
            telegramId: $from['id'],
            username: $from['username'],
            firstName: $from['first_name'] ?? null,
            lastName: $from['last_name'] ?? null,
            phoneNumber: $from['phone_number'] ?? null,
        );

        try {
            $this->telegramUserService->saveTelegramUser($this->validator, $user);
        } catch (TelegramUserAlreadyExistsException $e) {
        }

        try {
            $this->telegramUserService->changeTelegramUser($this->validator, $user);
        } catch (TelegramUserNoChangesDetectedException $e) {
        }
    }

    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(): Response
    {
        $response = $this->telegramBotResponseService->getStartMessage();

        return new Response($response->text);
    }
}
