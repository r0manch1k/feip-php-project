<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SummerHouseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SummerHouseRepository::class)]
#[ORM\Table(name: 'summer_houses')]
class SummerHouse extends House
{
    #[ORM\OneToMany(mappedBy: 'house', targetEntity: Booking::class, orphanRemoval: true)]
    private Collection $bookings;

    #[ORM\Column(nullable: true)]
    private ?int $bedrooms = null;

    #[ORM\Column(nullable: true)]
    private ?int $distanceFromSea = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hasShower = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hasBathroom = null;

    public function __construct(
        ?int $id,
        string $address,
        int $price,
        ?int $bedrooms = null,
        ?int $distanceFromSea = null,
        ?bool $hasShower = null,
        ?bool $hasBathroom = null,
    ) {
        parent::__construct($id, $address, $price);

        $this->bookings = new ArrayCollection();
        $this->bedrooms = $bedrooms;
        $this->distanceFromSea = $distanceFromSea;
        $this->hasShower = $hasShower;
        $this->hasBathroom = $hasBathroom;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function getBedrooms(): ?int
    {
        return $this->bedrooms;
    }

    public function setBedrooms(?int $bedrooms): static
    {
        $this->bedrooms = $bedrooms;

        return $this;
    }

    public function getDistanceFromSea(): ?int
    {
        return $this->distanceFromSea;
    }

    public function setDistanceFromSea(?int $distanceFromSea): static
    {
        $this->distanceFromSea = $distanceFromSea;

        return $this;
    }

    public function getHasShower(): ?bool
    {
        return $this->hasShower;
    }

    public function setHasShower(?bool $hasShower): static
    {
        $this->hasShower = $hasShower;

        return $this;
    }

    public function getHasBathroom(): ?bool
    {
        return $this->hasBathroom;
    }

    public function setHasBathroom(?bool $hasBathroom): static
    {
        $this->hasBathroom = $hasBathroom;

        return $this;
    }
}
