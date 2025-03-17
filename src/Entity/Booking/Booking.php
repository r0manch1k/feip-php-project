<?php

namespace App\Entity\Booking;

use App\Repository\BookingRepository;

class Booking
{
    private ?int $id = null;

    private ?string $phoneNumber = null;

    private ?int $houseId = null;

    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getHouseId(): ?int
    {
        return $this->houseId;
    }

    public function setHouseId(?int $houseId): static
    {
        $this->houseId = $houseId;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
