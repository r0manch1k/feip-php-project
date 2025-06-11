<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\BookingDto;
use App\Dto\SummerHouseDto;
use App\Dto\TelegramBotBoookingProgressDto;
use App\Dto\TelegramBotResponseDto;
use App\Entity\Booking;
use App\Enum\BookingStep;
use App\Exception\HouseAlreadyBookedException;
use App\Repository\TelegramBotUserRepository;
use DateTimeImmutable;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Twig\Environment;

/**
 * Service for handling responses to Telegram Bot messages.
 * It implements data manipulation and response generation based on user input.
 */
class TelegramBotResponseService
{
    public function __construct(
        private readonly SummerHouseService $summerHouseService,
        private readonly BookingService $bookingService,
        private readonly TelegramBotUserRepository $telegramBotUserRepository,
        private readonly TelegramBotUserService $telegramBotUserService,
        private readonly Environment $twig,
        private readonly LoggerInterface $telegramBotLogger,
        private readonly TelegramBotCacheService $telegramBotCacheService,
    ) {
    }

    /**
     * Resolves the message sent by user. Chooses the appropriate response.
     */
    public function getMessage(int $telegramId, string $messageText): TelegramBotResponseDto
    {
        $bookingProgress = $this->telegramBotCacheService->getBookingProgressCache(
            $telegramId,
            new TelegramBotBoookingProgressDto(step: BookingStep::None)
        );

        if (BookingStep::None != $bookingProgress->step && BookingStep::Done != $bookingProgress->step) {
            return $this->getBookHouseMessage($telegramId, null, $messageText);
        }

        return $this->getUnknownCommandMessage($telegramId);
    }

    public function getStartMessage(int $telegramId): TelegramBotResponseDto
    {
        try {
            $telegramUserDto = $this->telegramBotUserService->getTelegramBotUserByTelegramId($telegramId);
        } catch (Exception $e) {
            $this->telegramBotLogger->error('Error getting start message', [
                'telegramId' => $telegramId,
                'exception' => $e,
            ]);

            return $this->getRestartMessage($telegramId, 'An error occurred while processing your request.');
        }

        $inlineKeyboard = [
            [
                [
                    'text' => '📅 My Bookings',
                    'callback_data' => 'bookings_1',
                ],
                [
                    'text' => '🏠 Houses',
                    'callback_data' => 'houses_1',
                ],
            ],
            [
                [
                    'text' => 'ℹ️ Help',
                    'callback_data' => 'help',
                ],
            ],
        ];

        $replyMarkup = new InlineKeyboardMarkup($inlineKeyboard);

        $response = new TelegramBotResponseDto(
            text: $this->twig->render('messages/start.html.twig', [
                'telegramUser' => $telegramUserDto,
            ]),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML',
        );

        return $response;
    }

    public function getBookingsMessage(int $telegramId, int $page): TelegramBotResponseDto
    {
        try {
            $telegramBotUserDto = $this->telegramBotUserService->getTelegramBotUserByTelegramId($telegramId);
        } catch (Exception $e) {
            $this->telegramBotLogger->error('error getting start message', [
                'telegramId' => $telegramId,
                'exception' => $e,
            ]);

            return $this->getRestartMessage($telegramId, 'An error occurred while processing your request.');
        }

        // Caches bookings page so user can come back to it later.
        $this->telegramBotCacheService->getBookingsPageCache($telegramId, $page, true);

        $bookings = $this->bookingService->getBookings($telegramBotUserDto);

        $bookingsPerPage = 3;
        $pagesCount = (int) ceil(count($bookings) / $bookingsPerPage);

        if ($page < 1 || ($pagesCount > 0 && $page > $pagesCount)) {
            return $this->getRestartMessage($telegramId);
        }

        $offset = ($page - 1) * $bookingsPerPage;
        $bookingsOnPage = array_slice($bookings, $offset, $bookingsPerPage);

        $bookingButtons = [];
        foreach ($bookingsOnPage as $index => $booking) {
            $bookingButtons[] = [
                'text' => '📄 Booking #' . ($offset + (int) $index + 1),
                'callback_data' => 'booking_' . (string) $booking->id,
            ];
        }

        $navButtons = [];
        if ($page > 1) {
            $navButtons[] = [
                'text' => '⏪ Prev',
                'callback_data' => 'bookings_' . ($page - 1),
            ];
        }
        if ($page < $pagesCount) {
            $navButtons[] = [
                'text' => '⏩ Next',
                'callback_data' => 'bookings_' . ($page + 1),
            ];
        }

        $inlineKeyboard = [];

        if ($bookingButtons) {
            $inlineKeyboard[] = $bookingButtons;
        }

        if ($navButtons) {
            $inlineKeyboard[] = $navButtons;
        }

        $inlineKeyboard[] = [
            [
                'text' => '🏠 Back',
                'callback_data' => 'start',
            ],
        ];

        $replyMarkup = new InlineKeyboardMarkup($inlineKeyboard);

        return new TelegramBotResponseDto(
            text: $this->twig->render('messages/bookings.html.twig', [
                'bookings' => $bookingsOnPage,
                'page' => $page,
                'pagesCount' => $pagesCount,

            ]),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    public function getBookingMessage(int $telegramId, int $bookingId): TelegramBotResponseDto
    {
        try {
            $booking = $this->bookingService->getBookingById($bookingId);
        } catch (Exception $e) {
            $this->telegramBotLogger->error('error getting booking', [
                'telegramId' => $telegramId,
                'bookingId' => $bookingId,
                'exception' => $e,
            ]);

            return $this->getRestartMessage($telegramId, 'An error occurred while processing your request.');
        }

        $bookingPage = $this->telegramBotCacheService->getBookingsPageCache($telegramId, 1);

        $replyMarkup = new InlineKeyboardMarkup([
            [
                [
                    'text' => '✏️ Edit',
                    'callback_data' => 'edit_booking_' . $bookingId,
                ],
                [
                    'text' => '🗑️ Delete',
                    'callback_data' => 'delete_booking_' . $bookingId,
                ],
                [
                    'text' => '🏘 Back',
                    'callback_data' => 'bookings_' . $bookingPage,
                ],
            ],
        ]);

        return new TelegramBotResponseDto(
            text: $this->twig->render('messages/booking.html.twig', [
                'booking' => $booking,
            ]),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    public function getDeleteBookingMessage(int $telegramId, int $bookingId): TelegramBotResponseDto
    {
        try {
            $booking = $this->bookingService->getBookingById($bookingId);
        } catch (Exception $e) {
            $this->telegramBotLogger->error('error getting booking', [
                'telegramId' => $telegramId,
                'bookingId' => $bookingId,
                'exception' => $e,
            ]);

            return $this->getRestartMessage($telegramId, 'An error occurred while processing your request.');
        }

        $bookingPage = $this->telegramBotCacheService->getBookingsPageCache($telegramId, 1);

        $replyMarkup = new InlineKeyboardMarkup([
            [
                [
                    'text' => '✅ Confirm',
                    'callback_data' => 'confirm_delete_booking_' . $bookingId,
                ],
                [
                    'text' => '🏘 Back',
                    'callback_data' => 'bookings_' . $bookingPage,
                ],
            ],
        ]);

        return new TelegramBotResponseDto(
            text: $this->twig->render('messages/delete_booking.html.twig', [
                'booking' => $booking,
            ]),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    public function getConfirmDeleteBookingMessage(int $telegramId, int $bookingId): TelegramBotResponseDto
    {
        try {
            $this->bookingService->deleteBooking($bookingId);
        } catch (Exception $e) {
            $this->telegramBotLogger->error('error deleting booking', [
                'telegramId' => $telegramId,
                'bookingId' => $bookingId,
                'exception' => $e,
            ]);

            return $this->getRestartMessage($telegramId, 'An error occurred while processing your request.');
        }

        $bookingPage = $this->telegramBotCacheService->getBookingsPageCache($telegramId, 1);

        $replyMarkup = new InlineKeyboardMarkup([
            [
                [
                    'text' => '🏘 Back',
                    'callback_data' => 'bookings_' . $bookingPage,
                ],
            ],
        ]);

        return new TelegramBotResponseDto(
            text: '✅ Booking deleted successfully.',
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    public function getHousesMessage(int $telegramId, int $page = 1): TelegramBotResponseDto
    {
        try {
            $houses = $this->telegramBotCacheService->getHousesCache($telegramId);
        } catch (Exception $e) {
            $this->telegramBotLogger->error('error getting houses', [
                'telegramId' => $telegramId,
                'exception' => $e,
            ]);

            return $this->getRestartMessage($telegramId, 'An error occurred while processing your request.');
        }

        // Caches houses page so user can come back to it later.
        $this->telegramBotCacheService->getHousesPageCache($telegramId, $page, true);

        $housesPerPage = 3;

        $pagesCount = (int) ceil(count($houses) / 3);

        if ($page < 1 || ($pagesCount > 0 && $page > $pagesCount)) {
            return $this->getRestartMessage($telegramId);
        }

        $offset = ($page - 1) * $housesPerPage;

        $housesOnPage = array_slice($houses, $offset, $housesPerPage);

        $houseButtons = [];
        foreach ($housesOnPage as $index => $house) {
            if (null === $house->getId()) {
                $this->telegramBotLogger->error('house id is null', [
                    'house' => $house,
                ]);

                return $this->getRestartMessage($telegramId, 'An error occurred while processing your request.');
            }
            $houseButtons[] = [
                'text' => '🏡 House #' . ($offset + (int) $index + 1),
                'callback_data' => 'house_' . (string) $house->getId(),
            ];
        }

        $navButtons = [];

        if ($page > 1) {
            $navButtons[] = [
                'text' => '⏪ Prev',
                'callback_data' => 'houses_' . ($page - 1),
            ];
        }

        if ($page < $pagesCount) {
            $navButtons[] = [
                'text' => '⏩ Next',
                'callback_data' => 'houses_' . ($page + 1),
            ];
        }

        $inlineKeyboard = [
            $houseButtons,
            $navButtons,
            [
                [
                    'text' => '🔍 Find',
                    'callback_data' => 'find_house',
                ],
                [
                    'text' => '🏠 Back',
                    'callback_data' => 'start',
                ],
            ],
        ];

        $replyMarkup = new InlineKeyboardMarkup($inlineKeyboard);

        return new TelegramBotResponseDto(
            text: $this->twig->render('messages/houses.html.twig', [
                'houses' => $housesOnPage,
                'page' => $page,
                'pagesCount' => $pagesCount,
            ]),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    public function getHouseMessage(int $telegramId, int $houseId): TelegramBotResponseDto
    {
        // Deletes booking progress cache so user can start booking from the beginning.
        $this->telegramBotCacheService->deleteBookingProgressCache($telegramId);

        try {
            $house = $this->summerHouseService->getSummerHouseById($houseId);
        } catch (Exception $e) {
            $this->telegramBotLogger->error('error getting house', [
                'telegramId' => $telegramId,
                'houseId' => $houseId,
                'exception' => $e,
            ]);

            return $this->getRestartMessage($telegramId, 'An error occurred while processing your request.');
        }

        $housesPage = $this->telegramBotCacheService->getHousesPageCache($telegramId, 1);

        $replyMarkup = new InlineKeyboardMarkup([
            [
                [
                    'text' => '🔥 Book Now',
                    'callback_data' => 'book_house_' . $houseId,
                ],
                [
                    'text' => '🏘 Back',
                    'callback_data' => 'houses_' . $housesPage,
                ],
            ],
        ]);

        return new TelegramBotResponseDto(
            text: $this->twig->render('messages/house.html.twig', [
                'house' => $house,
            ]),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    public function getBookHouseMessage(
        int $telegramId,
        ?int $houseId = null,
        ?string $messageText = null,
    ): TelegramBotResponseDto {
        try {
            $telegramBotUserDto = $this->telegramBotUserService->getTelegramBotUserByTelegramId($telegramId);
        } catch (Exception $e) {
            $this->telegramBotLogger->error('error getting telegram bot user', [
                'telegramId' => $telegramId,
                'exception' => $e,
            ]);

            return $this->getRestartMessage($telegramId, 'An error occurred while processing your request.');
        }

        /**
         * Null in case if booking progress is already cached and method is called without houseId.
         *
         * @var SummerHouseDto|null $houseDto
         */
        $houseDto = null;

        if (null != $houseId) {
            try {
                $houseDto = $this->summerHouseService->getSummerHouseById($houseId);
            } catch (Exception $e) {
                return $this->getRestartMessage($telegramId, $e->getMessage());
            }
        }

        $bookingProgress = $this->telegramBotCacheService->getBookingProgressCache(
            $telegramId,
            new TelegramBotBoookingProgressDto(
                step: BookingStep::ChooseStartDate,
                houseDto: $houseDto,
                telegramBotUserDto: $telegramBotUserDto,
            )
        );

        if (BookingStep::ChooseStartDate === $bookingProgress->step) {
            // This step returns the message with asking for start date.

            if (null === $bookingProgress->houseDto || null === $bookingProgress->telegramBotUserDto) {
                $this->telegramBotLogger->error('house id or telegram user is null in booking progress', [
                    'bookingProgress' => $bookingProgress,
                ]);

                return $this->getRestartMessage($telegramId);
            }

            $replyMarkup = new InlineKeyboardMarkup([
                [
                    [
                        'text' => '🏘 Back',
                        'callback_data' => 'house_' . (string) $bookingProgress->houseDto->id,
                    ],
                ],
            ]);

            $todayDate = (new DateTimeImmutable())->format('Y-m-d');

            // Renders the message depending on the booking progress step.
            $response = new TelegramBotResponseDto(
                text: $this->twig->render('messages/book_house.html.twig', [
                    'bookingProgress' => $bookingProgress,
                    'todayDate' => $todayDate,
                ]),
                replyMarkup: $replyMarkup,
                parseMode: 'HTML'
            );

            // Go to the next step.
            $this->telegramBotCacheService->getBookingProgressCache(
                $telegramId,
                new TelegramBotBoookingProgressDto(
                    step: BookingStep::ChooseDaysAmount,
                    houseDto: $bookingProgress->houseDto,
                    telegramBotUserDto: $bookingProgress->telegramBotUserDto,
                ),
                true
            );

            return $response;
        } elseif (BookingStep::ChooseDaysAmount === $bookingProgress->step) {
            // This step validates given days amount and returns the message with asking for days amount.

            if (null === $bookingProgress->houseDto || null === $bookingProgress->telegramBotUserDto) {
                $this->telegramBotLogger->error('house id or telegram user is null in booking progress', [
                    'bookingProgress' => $bookingProgress,
                    'from' => $telegramBotUserDto,
                ]);

                return $this->getRestartMessage($telegramId);
            }

            if (null === $messageText) {
                return $this->getUnknownCommandMessage($telegramId, 'Please provide a valid start date.');
            }

            try {
                $startDate = $this->getValidatedStartDate($messageText);
            } catch (RuntimeException $e) {
                return $this->getUnknownCommandMessage(
                    $telegramId,
                    $e->getMessage() . ' You can enter the date again.'
                );
            }

            $replyMarkup = new InlineKeyboardMarkup([
                [
                    [
                        'text' => '🏘 Back',
                        'callback_data' => 'house_' . (string) $bookingProgress->houseDto->id,
                    ],
                ],
            ]);

            $response = new TelegramBotResponseDto(
                text: $this->twig->render('messages/book_house.html.twig', [
                    'bookingProgress' => $bookingProgress,
                ]),
                replyMarkup: $replyMarkup,
                parseMode: 'HTML'
            );

            // Go to the next step.
            $this->telegramBotCacheService->getBookingProgressCache(
                $telegramId,
                new TelegramBotBoookingProgressDto(
                    step: BookingStep::SetComment,
                    houseDto: $bookingProgress->houseDto,
                    telegramBotUserDto: $bookingProgress->telegramBotUserDto,
                    startDate: $startDate,
                ),
                true
            );

            return $response;
        } elseif (BookingStep::SetComment === $bookingProgress->step) {
            // This step sets the start date and returns the message with asking for comment.

            if (null === $bookingProgress->houseDto || null === $bookingProgress->telegramBotUserDto) {
                $this->telegramBotLogger->error('house id or telegram user is null in booking progress', [
                    'bookingProgress' => $bookingProgress,
                ]);

                return $this->getRestartMessage($telegramId);
            }

            if (null === $bookingProgress->startDate) {
                $this->telegramBotLogger->error('start date is null in set comment step', [
                    'bookingProgress' => $bookingProgress,
                    'from' => $telegramBotUserDto,
                ]);

                return $this->getRestartMessage($telegramId);
            }

            if (null === $messageText) {
                return $this->getUnknownCommandMessage($telegramId, 'Please provide a valid days amount.');
            }

            try {
                $daysAmount = $this->getValidatedDaysAmount($messageText);
            } catch (RuntimeException $e) {
                return $this->getUnknownCommandMessage(
                    $telegramId,
                    $e->getMessage() . ' You can enter the days amount again.'
                );
            }

            $endDate = $this->getEndDateByDaysAmount(
                new DateTimeImmutable($bookingProgress->startDate->format('Y-m-d')),
                $daysAmount
            );

            // Calculates total price based on the house price and days amount.
            $totalPrice = $bookingProgress->houseDto->price * $daysAmount;

            $bookingProgress->totalPrice = $totalPrice;
            $bookingProgress->endDate = $endDate;

            $replyMarkup = new InlineKeyboardMarkup([
                [
                    [
                        'text' => '🏘 Back',
                        'callback_data' => 'house_' . (string) $bookingProgress->houseDto->id,
                    ],
                ],
            ]);

            $response = new TelegramBotResponseDto(
                text: $this->twig->render('messages/book_house.html.twig', [
                    'bookingProgress' => $bookingProgress,
                ]),
                replyMarkup: $replyMarkup,
                parseMode: 'HTML'
            );

            // Go to the next step.
            $this->telegramBotCacheService->getBookingProgressCache(
                $telegramId,
                new TelegramBotBoookingProgressDto(
                    step: BookingStep::Confirm,
                    houseDto: $bookingProgress->houseDto,
                    telegramBotUserDto: $bookingProgress->telegramBotUserDto,
                    startDate: $bookingProgress->startDate,
                    endDate: $endDate,
                    totalPrice: $totalPrice,
                ),
                true
            );

            return $response;
        } elseif (BookingStep::Confirm === $bookingProgress->step) {
            // This step confirms the booking and returns the message with asking for comment.

            if (null === $bookingProgress->houseDto || null === $bookingProgress->telegramBotUserDto) {
                $this->telegramBotLogger->error('house id or telegram user is null in booking progress', [
                    'bookingProgress' => $bookingProgress,
                ]);

                return $this->getRestartMessage($telegramId);
            }

            if (
                null === $bookingProgress->startDate
                || null === $bookingProgress->endDate
                || null === $bookingProgress->totalPrice
            ) {
                $this->telegramBotLogger->error('start date, end date or total price is null in booking progress', [
                    'bookingProgress' => $bookingProgress,
                ]);

                return $this->getRestartMessage($telegramId);
            }

            if (null === $messageText) {
                return $this->getUnknownCommandMessage($telegramId, 'Please provide a valid comment.');
            }

            try {
                $comment = $this->getValidatedComment($messageText);
            } catch (RuntimeException $e) {
                return $this->getUnknownCommandMessage(
                    $telegramId,
                    $e->getMessage() . ' You can enter the comment again.'
                );
            }

            // Sets the comment to the booking progress.
            $bookingProgress->comment = $comment;

            // Renders the message with booking confirmation.
            $replyMarkup = new InlineKeyboardMarkup([
                [
                    [
                        'text' => '✅ Confirm',
                        'callback_data' => 'confirm_booking',
                    ],
                    [
                        'text' => '🏘 Back',
                        'callback_data' => 'house_' . (string) $bookingProgress->houseDto->id,
                    ],
                ],
            ]);

            $response = new TelegramBotResponseDto(
                text: $this->twig->render('messages/book_house.html.twig', [
                    'bookingProgress' => $bookingProgress,
                ]),
                replyMarkup: $replyMarkup,
                parseMode: 'HTML'
            );

            // Go to the next step.
            $this->telegramBotCacheService->getBookingProgressCache(
                $telegramId,
                new TelegramBotBoookingProgressDto(
                    step: BookingStep::Done,
                    houseDto: $bookingProgress->houseDto,
                    telegramBotUserDto: $bookingProgress->telegramBotUserDto,
                    startDate: $bookingProgress->startDate,
                    endDate: $bookingProgress->endDate,
                    totalPrice: $bookingProgress->totalPrice,
                    comment: $bookingProgress->comment,
                ),
                true
            );

            return $response;
        } elseif (BookingStep::Done === $bookingProgress->step) {
            // This step confirms the booking and returns the message with booking done.

            if (null === $bookingProgress->houseDto || null === $bookingProgress->telegramBotUserDto) {
                $this->telegramBotLogger->error('house id or telegram user is null in booking progress', [
                    'bookingProgress' => $bookingProgress,
                ]);

                return $this->getRestartMessage($telegramId);
            }

            if (
                null === $bookingProgress->startDate
                || null === $bookingProgress->endDate
                || null === $bookingProgress->totalPrice
            ) {
                $this->telegramBotLogger->error('start date, end date or total price is null in booking progress', [
                    'bookingProgress' => $bookingProgress,
                ]);

                return $this->getRestartMessage($telegramId);
            }

            // Saves the booking.
            try {
                // TODO: Make users dto fields in BookingDto instead of entities.
                $telegramBotUser = $this->telegramBotUserRepository->findOneBy(['telegramId' => $telegramId]);

                if (null === $bookingProgress->houseDto->id) {
                    throw new RuntimeException('house id is null in booking progress.');
                }

                $bookingDto = new BookingDto(
                    id: null,
                    user: null,
                    telegramBotUser: $telegramBotUser,
                    houseId: $bookingProgress->houseDto->id,
                    startDate: $bookingProgress->startDate,
                    endDate: $bookingProgress->endDate,
                    comment: $bookingProgress->comment
                );
                $this->bookingService->saveBooking($bookingDto);
            } catch (HouseAlreadyBookedException $e) {
                $this->telegramBotLogger->error('house already booked', [
                    'exception' => $e,
                    'bookingProgress' => $bookingProgress,
                ]);

                return $this->getRestartMessage(
                    $telegramId,
                    'This house is already booked for the selected dates. Please choose another date.'
                );
            } catch (Exception $e) {
                $this->telegramBotLogger->error('error saving booking', [
                    'exception' => $e,
                    'bookingProgress' => $bookingProgress,
                ]);

                return $this->getRestartMessage($telegramId, 'An error occurred while processing your request.');
            }

            $this->telegramBotLogger->info('successfully booked', [
                'bookingProgress' => $bookingProgress,
                'from' => $telegramBotUserDto,
            ]);

            $replyMarkup = new InlineKeyboardMarkup([
                [
                    [
                        'text' => '📅 My Bookings',
                        'callback_data' => 'bookings_1',
                    ],
                    [
                        'text' => '🏘 Back',
                        'callback_data' => 'house_' . (string) $bookingProgress->houseDto->id,
                    ],
                ],
            ]);

            $response =  new TelegramBotResponseDto(
                text: $this->twig->render('messages/book_house.html.twig', [
                    'bookingProgress' => $bookingProgress,
                ]),
                replyMarkup: $replyMarkup,
                parseMode: 'HTML'
            );

            $this->telegramBotCacheService->invalidateTelegramBotUserCache($telegramId);

            return $response;
        }

        return $this->getUnknownCommandMessage($telegramId, 'Not implemented step: ' . $bookingProgress->step->value);
    }

    public function getUnknownCommandMessage(int $telegramId, ?string $message = null): TelegramBotResponseDto
    {
        $replyMarkup = new InlineKeyboardMarkup([
            [
                ['text' => '🏠 Menu', 'callback_data' => 'start'],
            ],
        ]);

        return new TelegramBotResponseDto(
            text: $this->twig->render('messages/unknown_command.html.twig', [
                'message' => $message,
            ]),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    public function getRestartMessage(int $telegramId, ?string $message = null): TelegramBotResponseDto
    {
        $this->telegramBotCacheService->invalidateTelegramBotUserCache($telegramId);

        $replyMarkup = new InlineKeyboardMarkup([
            [
                ['text' => '🏠 Back', 'callback_data' => 'start'],
            ],
        ]);

        return new TelegramBotResponseDto(
            text: $this->twig->render('messages/restart.html.twig', [
                'message' => $message,
            ]),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    public function getHelpMessage(int $telegramId): TelegramBotResponseDto
    {
        $replyMarkup = new InlineKeyboardMarkup([
            [
                ['text' => '🏠 Back', 'callback_data' => 'start'],
            ],
        ]);

        return new TelegramBotResponseDto(
            text: $this->twig->render('messages/help.html.twig'),
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    private function getValidatedStartDate(string $startDateRaw): DateTimeImmutable
    {
        try {
            $startDate = DateTimeImmutable::createFromFormat('Y-m-d', $startDateRaw);
        } catch (Exception $e) {
            $this->telegramBotLogger->error('invalid start date format', [
                'startDate' => $startDateRaw,
                'exception' => $e,
            ]);
            throw new RuntimeException('Invalid start date format.');
        }

        if (false === $startDate) {
            $this->telegramBotLogger->error('invalid start date format', [
                'startDate' => $startDateRaw,
            ]);

            throw new RuntimeException('Invalid start date format.');
        }

        if ($startDate < new DateTimeImmutable('today')) {
            throw new RuntimeException('Start date cannot be in the past.');
        }

        return $startDate;
    }

    public function getValidatedDaysAmount(string $daysAmountRaw): int
    {
        if (!is_numeric($daysAmountRaw) || (int) $daysAmountRaw <= 0) {
            throw new RuntimeException('Invalid days amount. Please enter a positive number.');
        }

        if ((int) $daysAmountRaw > 365) {
            throw new RuntimeException('Days amount cannot be more than 365.');
        }

        return (int) $daysAmountRaw;
    }

    public function getValidatedComment(string $commentRaw): ?string
    {
        if ('-' === trim($commentRaw)) {
            return null;
        }

        if (strlen($commentRaw) > 255) {
            throw new RuntimeException('Comment is too long. Maximum length is 255 characters.');
        }

        return $commentRaw;
    }

    /**
     * Supposes that start date is already validated.
     */
    public function getEndDateByDaysAmount(DateTimeImmutable $startDate, int $daysAmount): DateTimeImmutable
    {
        $endDate = $startDate->modify('+' . $daysAmount . ' days');

        return $endDate;
    }
}
