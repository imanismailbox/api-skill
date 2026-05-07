<?php

declare(strict_types=1);

namespace App\Http\Payloads\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
    ]
)]
final class LoginPayload
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
