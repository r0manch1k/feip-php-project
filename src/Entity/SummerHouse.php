<?php

namespace App\Entity;

use App\Entity\House;
use App\Repository\SummerHouseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SummerHouseRepository::class)]
class SummerHouse extends House
{
    #[ORM\Column(nullable: true)]
    private ?int $bedrooms = null;

    #[ORM\Column(nullable: true)]
    private ?int $distanceFromSea = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hasShower = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hasBathroom = null;

    public function __construct(
        int $id,
        ?string $address = null,
        ?int $price = null,
        ?int $bedrooms = null,
        ?int $distanceFromSea = null,
        ?bool $hasShower = null,
        ?bool $hasBathroom = null
    ) {
        parent::__construct($id, $address, $price);

        $this->bedrooms = $bedrooms;
        $this->distanceFromSea = $distanceFromSea;
        $this->hasShower = $hasShower;
        $this->hasBathroom = $hasBathroom;
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

    public function hasShower(): ?bool
    {
        return $this->hasShower;
    }

    public function setHasShower(?bool $hasShower): static
    {
        $this->hasShower = $hasShower;

        return $this;
    }

    public function hasBathroom(): ?bool
    {
        return $this->hasBathroom;
    }

    public function setHasBathroom(?bool $hasBathroom): static
    {
        $this->hasBathroom = $hasBathroom;

        return $this;
    }
}
