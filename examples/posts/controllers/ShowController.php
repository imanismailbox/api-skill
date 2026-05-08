<?php

declare(strict_types=1);

namespace App\Http\Controllers\Posts\V1;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class ShowController
{
    #[OA\Get(
        path: '/v1/posts/{post}',
        operationId: 'postsShow',
        summary: 'Show post',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'post', required: true, schema: new OA\Schema(type: 'string', format: 'ulid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post retrieved',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Success'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/Post'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 404, description: 'Post not found', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
        ]
    )]
    public function __invoke(Post $post): JsonResponse
    {
        return new JsonResponse(
            data: [
                'status' => true,
                'message' => 'Success',
                'data' => new PostResource($post),
            ],
            status: Response::HTTP_OK,
        );
    }
}
