<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\TelegramBotBoookingProgressDto;
use App\Entity\SummerHouse;
use App\Repository\SummerHouseRepository;
use App\Repository\TelegramBotUserRepository;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twig\Environment;

class TelegramBotCacheService
{
    public function __construct(
        private readonly TelegramBotUserRepository $telegramBotUserRepository,
        private readonly TelegramBotService $telegramBotService,
        private readonly BookingService $bookingService,
        private readonly SummerHouseRepository $summerHouseRepository,
        private readonly Environment $twig,
        private readonly TagAwareCacheInterface $cacheTelegramBot,
        private readonly LoggerInterface $telegramBotLogger,
    ) {
    }

    /**
     * Get (or set the given one) booking progress instance.
     */
    public function getBookingProgressCache(int $telegramId, TelegramBotBoookingProgressDto $bookingProgress, bool $delete = false): TelegramBotBoookingProgressDto
    {
        if ($delete) {
            $this->cacheTelegramBot->delete($telegramId . '_booking_progress');
        }

        $bookingProgressCached = $this->cacheTelegramBot->get(
            $telegramId . '_booking_progress',
            function (ItemInterface $item) use ($telegramId, $bookingProgress) {
                $item->tag([(string) $telegramId]);
                $item->expiresAfter(600);

                return $bookingProgress;
            }
        );

        return $bookingProgressCached;
    }

    public function deleteBookingProgressCache(int $telegramId): void
    {
        $this->cacheTelegramBot->delete($telegramId . '_booking_progress');
    }

    /**
     * @return SummerHouse[]
     */
    public function getHousesCache(int $telegramId, bool $delete = false): array
    {
        if ($delete) {
            $this->cacheTelegramBot->delete($telegramId . '_houses');
        }

        $houses = $this->cacheTelegramBot->get($telegramId . '_houses', function (ItemInterface $item) use ($telegramId) {
            $item->tag([(string) $telegramId]);

            $item->expiresAfter(300);

            $currentDatetime = (new DateTimeImmutable())->setTimestamp(time());

            return $this->summerHouseRepository->getUnbookedHouses($currentDatetime);
        });

        return $houses;
    }

    public function getBookingsPageCache(int $telegramId, int $page, bool $delete = false): int
    {
        if ($delete) {
            $this->cacheTelegramBot->delete($telegramId . '_bookings_page_' . $page);
        }

        $page = $this->cacheTelegramBot->get($telegramId . '_bookings_page', function (ItemInterface $item) use ($telegramId, $page) {
            $item->tag([(string) $telegramId]);
            $item->expiresAfter(300);

            return $page;
        });

        return $page;
    }

    public function getHousesPageCache(int $telegramId, int $page, bool $delete = false): int
    {
        if ($delete) {
            $this->cacheTelegramBot->delete($telegramId . '_houses_page_' . $page);
        }

        $page = $this->cacheTelegramBot->get($telegramId . '_houses_page', function (ItemInterface $item) use ($telegramId, $page) {
            $item->tag([(string) $telegramId]);
            $item->expiresAfter(300);

            return $page;
        });

        return $page;
    }

    public function invalidateTelegramBotUserCache(int $telegramId): void
    {
        $this->cacheTelegramBot->invalidateTags([(string) $telegramId]);
    }
}
