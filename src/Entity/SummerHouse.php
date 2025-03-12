<?php

namespace App\Entity;

use App\Repository\SummerHouseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\House;

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
