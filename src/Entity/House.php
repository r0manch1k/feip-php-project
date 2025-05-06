<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\HouseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HouseRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\Table(name: 'house')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['house' => House::class, 'summer_house' => SummerHouse::class])]
class House
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(length: 255, nullable: false)]
    private string $address;

    #[ORM\Column(nullable: false)]
    private int $price;

    public function __construct(
        ?int $id,
        string $address,
        int $price,
    ) {
        $this->id = $id;
        $this->address = $address;
        $this->price = $price;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }
}
