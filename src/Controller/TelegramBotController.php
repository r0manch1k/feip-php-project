<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\TelegramBotUserDto;
use App\Exception\TelegramBotUserAlreadyExistsException;
use App\Exception\TelegramBotUserNoChangesDetectedException;
use App\Service\TelegramBotCacheService;
use App\Service\TelegramBotResponseService;
use App\Service\TelegramBotService;
use App\Service\TelegramBotUserService;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class TelegramBotController
{
    public function __construct(
        private readonly TelegramBotService $telegramBotService,
        private readonly TelegramBotUserService $TelegramBotUserService,
        private readonly ValidatorInterface $validator,
        private readonly TelegramBotResponseService $telegramBotResponseService,
        private readonly TelegramBotCacheService $telegramBotCacheService,
        private readonly LoggerInterface $telegramBotLogger,
    ) {
    }

    #[Route('/telegram/webhook', name: 'telegram_webhook', methods: ['POST'])]
    public function handle(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['callback_query'])) {
            $this->handleCallback($request);

            return new Response('OK');
        }

        $bot = $this->telegramBotService->getBot();

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

        if (!isset($from['id']) || !isset($from['username'])) {
            $this->telegramBotLogger->error('user id or username is missing', [
                'from' => $from,
                'data' => $data,
            ]);

            return new Response('OK');
        }

        /**
         * @var int $telegramId
         */
        $telegramId = $from['id'];

        try {
            $this->saveUser($from);
        } catch (Exception $e) {
            $this->telegramBotLogger->error('failed to save telegram user', [
                'exception' => $e,
                'from' => $from,
            ]);

            return new Response('OK');
        }

        if ('/start' === $text) {
            try {
                // Delete any cache for this user on /start
                $this->telegramBotCacheService->invalidateTelegramBotUserCache($telegramId);

                $response = $this->telegramBotResponseService->getStartMessage($telegramId);
                $bot->sendMessage(
                    chatId: $chatId,
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
                $this->telegramBotLogger->info('start message sent', [
                    'from' => $from,
                    'telegram_id' => $telegramId,
                ]);
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to send start message', [
                    'exception' => $e,
                    'from' => $from,
                ]);

                return new Response('OK');
            }

        } else {
            try {
                /**
                 * Handler for any other text message.
                 */
                $response = $this->telegramBotResponseService->getMessage(
                    telegramId: $telegramId,
                    messageText: $text,
                );
                $bot->sendMessage(
                    chatId: $chatId,
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to send message', [
                    'exception' => $e,
                    'from' => $from,
                    'text' => $text,
                ]);
            }
        }

        return new Response('OK');
    }

    private function handleCallback(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        /**
         * @var array $callbackQuery
         */
        $callbackQuery = $data['callback_query'];

        if (!isset($callbackQuery['from'])) {
            $this->telegramBotLogger->error('user id or username is missing in callback', [
                'callback_query' => $callbackQuery,
                'data' => $data,
            ]);

            return new Response('OK');
        }

        /*
         * @var array $from
         */
        $from = $callbackQuery['from'];

        if (!isset($from['id'])) {
            $this->telegramBotLogger->error('user id is missing in callback', [
                'from' => $callbackQuery['from'],
                'data' => $data,
            ]);

            return new Response('OK');
        }

        /**
         * @var int $telegramId
         */
        $telegramId = $from['id'];

        $bot = $this->telegramBotService->getBot();

        if (!isset($callbackQuery['data'])) {
            $this->telegramBotLogger->error('callback_query data is missing', [
                'callback_query' => $callbackQuery,
                'data' => $data,
            ]);

            return new Response('OK');
        }

        $button = $callbackQuery['data'];

        if (!isset($callbackQuery['message']['chat']['id']) || !isset($callbackQuery['message']['message_id'])) {
            $this->telegramBotLogger->error('chat id or message id is missing in callback', [
                'callback_query' => $callbackQuery,
                'data' => $data,
            ]);

            return new Response('OK');
        }

        if ('start' === $button) {
            try {
                $response = $this->telegramBotResponseService->getStartMessage($telegramId);

                if (!($response->replyMarkup instanceof InlineKeyboardMarkup) && null !== $response->replyMarkup) {
                    $this->telegramBotLogger->error('reply markup is not an instance of InlineKeyboardMarkup', [
                        'reply_markup' => $response->replyMarkup,
                    ]);

                    $response->replyMarkup = null;
                }

                $bot->editMessageText(
                    chatId: $callbackQuery['message']['chat']['id'],
                    messageId: $callbackQuery['message']['message_id'],
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to send start message', [
                    'exception' => $e,
                    'from' => $from,
                ]);
            }
        } elseif (str_starts_with($button, 'bookings_')) {
            $page = (int) substr($button, 9);

            try {
                $response = $this->telegramBotResponseService->getBookingsMessage($telegramId, $page);

                if (!($response->replyMarkup instanceof InlineKeyboardMarkup) && null !== $response->replyMarkup) {
                    $this->telegramBotLogger->error('reply markup is not an instance of InlineKeyboardMarkup', [
                        'reply_markup' => $response->replyMarkup,
                    ]);

                    $response->replyMarkup = null;
                }

                $bot->editMessageText(
                    chatId: $callbackQuery['message']['chat']['id'],
                    messageId: $callbackQuery['message']['message_id'],
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to send bookings message', [
                    'exception' => $e,
                    'from' => $from,
                ]);
            }
        } elseif (str_starts_with($button, 'houses_')) {
            $page = (int) substr($button, 7);

            try {
                $response = $this->telegramBotResponseService->getHousesMessage($telegramId, $page);

                $replyMarkup = $response->replyMarkup;

                if (!($replyMarkup instanceof InlineKeyboardMarkup) && null !== $replyMarkup) {
                    $this->telegramBotLogger->error('reply markup is not an instance of InlineKeyboardMarkup', [
                        'reply_markup' => $replyMarkup,
                    ]);

                    $replyMarkup = null;
                }

                $bot->editMessageText(
                    chatId: $callbackQuery['message']['chat']['id'],
                    messageId: $callbackQuery['message']['message_id'],
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to send houses message', [
                    'exception' => $e,
                    'from' => $from,
                ]);
            }
        } elseif (str_starts_with($button, 'house_')) {
            $houseId = (int) substr($button, 6);

            try {
                $response = $this->telegramBotResponseService->getHouseMessage($telegramId, $houseId);

                if (!($response->replyMarkup instanceof InlineKeyboardMarkup) && null !== $response->replyMarkup) {
                    $this->telegramBotLogger->error('reply markup is not an instance of InlineKeyboardMarkup', [
                        'reply_markup' => $response->replyMarkup,
                    ]);

                    $response->replyMarkup = null;
                }

                $bot->editMessageText(
                    chatId: $callbackQuery['message']['chat']['id'],
                    messageId: $callbackQuery['message']['message_id'],
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to send house message', [
                    'exception' => $e,
                    'from' => $from,
                ]);
            }
        } elseif (str_starts_with($button, 'book_house_')) {
            $houseId = (int) substr($button, 11);

            try {
                $response = $this->telegramBotResponseService->getBookHouseMessage($telegramId, $houseId);

                if (!($response->replyMarkup instanceof InlineKeyboardMarkup) && null !== $response->replyMarkup) {
                    $this->telegramBotLogger->error('reply markup is not an instance of InlineKeyboardMarkup', [
                        'reply_markup' => $response->replyMarkup,
                    ]);

                    $response->replyMarkup = null;
                }

                $bot->editMessageText(
                    chatId: $callbackQuery['message']['chat']['id'],
                    messageId: $callbackQuery['message']['message_id'],
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to send book house message', [
                    'exception' => $e,
                    'from' => $from,
                ]);
            }
        } elseif ('confirm_booking' === $button) {
            try {
                $response = $this->telegramBotResponseService->getBookHouseMessage($telegramId);

                if (!($response->replyMarkup instanceof InlineKeyboardMarkup) && null !== $response->replyMarkup) {
                    $this->telegramBotLogger->error('reply markup is not an instance of InlineKeyboardMarkup', [
                        'reply_markup' => $response->replyMarkup,
                    ]);

                    $response->replyMarkup = null;
                }

                $bot->editMessageText(
                    chatId: $callbackQuery['message']['chat']['id'],
                    messageId: $callbackQuery['message']['message_id'],
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to confirm booking', [
                    'exception' => $e,
                    'from' => $from,
                ]);
            }
        } elseif (str_starts_with($button, 'booking_')) {
            $bookingId = (int) substr($button, 8);

            try {
                $response = $this->telegramBotResponseService->getBookingMessage($telegramId, $bookingId);

                if (!($response->replyMarkup instanceof InlineKeyboardMarkup) && null !== $response->replyMarkup) {
                    $this->telegramBotLogger->error('reply markup is not an instance of InlineKeyboardMarkup', [
                        'reply_markup' => $response->replyMarkup,
                    ]);

                    $response->replyMarkup = null;
                }

                $bot->editMessageText(
                    chatId: $callbackQuery['message']['chat']['id'],
                    messageId: $callbackQuery['message']['message_id'],
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to send booking message', [
                    'exception' => $e,
                    'from' => $from,
                ]);
            }
        } elseif (str_starts_with($button, 'delete_booking_')) {
            $bookingId = (int) substr($button, 15);

            try {
                $response = $this->telegramBotResponseService->getDeleteBookingMessage($telegramId, $bookingId);

                if (!($response->replyMarkup instanceof InlineKeyboardMarkup) && null !== $response->replyMarkup) {
                    $this->telegramBotLogger->error('reply markup is not an instance of InlineKeyboardMarkup', [
                        'reply_markup' => $response->replyMarkup,
                    ]);

                    $response->replyMarkup = null;
                }

                $bot->editMessageText(
                    chatId: $callbackQuery['message']['chat']['id'],
                    messageId: $callbackQuery['message']['message_id'],
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to delete booking', [
                    'exception' => $e,
                    'from' => $from,
                ]);
            }
        } elseif (str_starts_with($button, 'confirm_delete_booking_')) {
            $bookingId = (int) substr($button, 23);

            try {
                $response = $this->telegramBotResponseService->getConfirmDeleteBookingMessage($telegramId, $bookingId);

                if (!($response->replyMarkup instanceof InlineKeyboardMarkup) && null !== $response->replyMarkup) {
                    $this->telegramBotLogger->error('reply markup is not an instance of InlineKeyboardMarkup', [
                        'reply_markup' => $response->replyMarkup,
                    ]);

                    $response->replyMarkup = null;
                }

                $bot->editMessageText(
                    chatId: $callbackQuery['message']['chat']['id'],
                    messageId: $callbackQuery['message']['message_id'],
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to confirm delete booking', [
                    'exception' => $e,
                    'from' => $from,
                ]);
            }
        } elseif ('help' === $button) {
            try {
                $response = $this->telegramBotResponseService->getHelpMessage($telegramId);

                if (!($response->replyMarkup instanceof InlineKeyboardMarkup) && null !== $response->replyMarkup) {
                    $this->telegramBotLogger->error('reply markup is not an instance of InlineKeyboardMarkup', [
                        'reply_markup' => $response->replyMarkup,
                    ]);

                    $response->replyMarkup = null;
                }

                $bot->editMessageText(
                    chatId: $callbackQuery['message']['chat']['id'],
                    messageId: $callbackQuery['message']['message_id'],
                    text: $response->text,
                    parseMode: $response->parseMode,
                    replyMarkup: $response->replyMarkup
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('failed to send help message', [
                    'exception' => $e,
                    'from' => $from,
                ]);
            }
        } else {
            $this->telegramBotLogger->error('unknown callback button', [
                'button' => $button,
                'data' => $data,
            ]);
        }

        return new Response('OK');
    }

    private function saveUser(array $from): void
    {
        if (!isset($from['id']) || !isset($from['username'])) {
            throw new InvalidArgumentException('User ID is required');
        }

        $user = new TelegramBotUserDto(
            id: null,
            telegramId: $from['id'],
            username: $from['username'],
            firstName: $from['first_name'] ?? null,
            lastName: $from['last_name'] ?? null,
            phoneNumber: $from['phone_number'] ?? null,
        );

        try {
            $this->TelegramBotUserService->saveTelegramBotUser($this->validator, $user);
        } catch (TelegramBotUserAlreadyExistsException $e) {
        }

        try {
            $this->TelegramBotUserService->changeTelegramBotUser($this->validator, $user);
        } catch (TelegramBotUserNoChangesDetectedException $e) {
        }
    }

    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(Request $request, TagAwareCacheInterface $cacheTelegramBot): Response
    {
        // $valueSet = $cacheTelegramBot->get('item_0', function (ItemInterface $item): string {
        //     $item->tag(['foo', 'bar']);

        //     return 'cached_' . time();
        // });

        // $valueAfterInvalidation = $cacheTelegramBot->get('item_0', function (ItemInterface $item): string {
        //     $item->tag(['foo', 'bar']);

        //     return 're-cached_' . time() + 1;
        // });

        // $cacheTelegramBot->invalidateTags(['foo']);

        // return new Response("Before invalidation: $valueSet | After invalidation: $valueAfterInvalidation");

        // return new Response($response->text);

        return new Response('OK');
    }
}
