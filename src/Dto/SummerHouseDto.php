<?php

declare(strict_types=1);

namespace App\Dto;

readonly class SummerHouseDto extends HouseDto
{
    public function __construct(
        public int $price,
        public string $address,
        public ?int $id = null,
        public ?int $bedrooms = null,
        public ?int $distanceFromSea = null,
        public ?bool $hasShower = null,
        public ?bool $hasBathroom = null,
    ) {
    }
}
