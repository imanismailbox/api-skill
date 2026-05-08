<?php

declare(strict_types=1);

namespace App\Http\Payloads\Posts;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PostUpdateRequest',
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255),
        new OA\Property(property: 'content', type: 'string'),
    ]
)]
final class UpdatePayload
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $content = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'title'   => $this->title,
            'content' => $this->content,
        ]);
    }
}
