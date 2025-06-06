<?php

declare(strict_types=1);

namespace App\Dto;

readonly class SummerHouseDto extends HouseDto
{
    public function __construct(
        public ?int $id,
        public string $address,
        public int $price,
        public ?int $bedrooms,
        public ?int $distanceFromSea,
        public ?bool $hasShower,
        public ?bool $hasBathroom,
    ) {
    }
}
