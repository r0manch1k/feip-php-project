<?php

declare(strict_types=1);

namespace App\Dto\Auth;

use Symfony\Component\Serializer\Annotation\Groups;

readonly class TokenDto
{
    public function __construct(
        #[Groups(['login'])]
        public string $token,
        #[Groups(['login', 'refresh'])]
        public string $refreshToken,
    ) {
    }
}
