<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

readonly class UserDto
{
    public function __construct(
        #[Groups(['internal'])]
        public string $password,
        #[Groups(['public'])]
        public array $roles = [],
        #[Groups(['public'])]
        public ?int $id = null,
        #[Groups(['public'])]
        public ?string $phoneNumber,
    ) {
    }
}
