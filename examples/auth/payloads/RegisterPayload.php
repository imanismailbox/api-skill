<?php

declare(strict_types=1);

namespace App\Http\Payloads\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['name', 'email', 'password'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
    ]
)]
final class RegisterPayload
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}

    /**
     * Maps to Eloquent. The User model must cast 'password' to 'hashed'
     * — this value is stored as-is and the cast handles hashing on write.
     */
    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => $this->password,
        ];
    }
}
