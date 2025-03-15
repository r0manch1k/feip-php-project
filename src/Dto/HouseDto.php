<?php

declare(strict_types=1);

namespace App\Dto;

readonly class HouseDto
{
    public function __construct(
        public int $id,
        public string $address,
        public int $price,
    ) {}
}
