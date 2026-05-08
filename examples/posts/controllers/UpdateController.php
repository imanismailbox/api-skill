<?php

declare(strict_types=1);

namespace App\Http\Controllers\Posts\V1;

use App\Actions\Posts\UpdatePostAction;
use App\Http\Requests\Posts\V1\UpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class UpdateController
{
    public function __construct(
        private readonly UpdatePostAction $action,
    ) {}

    #[OA\Put(
        path: '/v1/posts/{post}',
        operationId: 'postsUpdate',
        summary: 'Update post',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'post', required: true, schema: new OA\Schema(type: 'string', format: 'ulid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/PostUpdateRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post updated',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Success'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/Post'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 404, description: 'Post not found', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
        ]
    )]
    public function __invoke(Post $post, UpdateRequest $request): JsonResponse
    {
        $post = $this->action->handle(
            post: $post,
            payload: $request->payload(),
        );

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
