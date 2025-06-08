<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookingRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\Table(name: 'bookings')]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: TelegramBotUser::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?TelegramBotUser $telegramBotUser = null;

    #[ORM\ManyToOne(targetEntity: SummerHouse::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', name: 'house_id', referencedColumnName: 'id')]
    private SummerHouse $house;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $startDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $endDate;

    public function __construct(
        ?int $id,
        ?User $user,
        ?TelegramBotUser $telegramBotUser,
        SummerHouse $house,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        ?string $comment = null,
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->telegramBotUser = $telegramBotUser;
        $this->house = $house;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->comment = $comment;
    }

    public function getIsActive(): bool
    {
        $now = new DateTimeImmutable();

        return $this->startDate <= $now && $this->endDate >= $now;
    }

    public function getTotalPrice(): float
    {
        return $this->house->getPrice() * $this->getBookingDuration();
    }

    public function getBookingDuration(): int
    {
        return $this->startDate->diff($this->endDate)->days;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getTelegramBotUser(): ?TelegramBotUser
    {
        return $this->telegramBotUser;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function setTelegramBotUser(?TelegramBotUser $telegramBotUser): static
    {
        $this->telegramBotUser = $telegramBotUser;

        return $this;
    }

    public function getHouse(): SummerHouse
    {
        return $this->house;
    }

    public function setHouse(SummerHouse $house): static
    {
        $this->house = $house;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }
}
