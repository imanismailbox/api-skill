<?php

declare(strict_types=1);

namespace App\Http\Payloads\Posts;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PostStoreRequest',
    required: ['title', 'content'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255),
        new OA\Property(property: 'content', type: 'string'),
    ]
)]
final class StorePayload
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly string $userId,
    ) {}

    public function toArray(): array
    {
        return [
            'title'   => $this->title,
            'content' => $this->content,
            'user_id' => $this->userId,
        ];
    }
}
