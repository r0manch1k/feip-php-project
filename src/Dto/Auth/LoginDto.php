<?php

declare(strict_types=1);

namespace App\Dto\Auth;

readonly class LoginDto
{
    public function __construct(
        public string $phoneNumber,
        public string $password,
    ) {
    }
}
