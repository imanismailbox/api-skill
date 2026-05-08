<?php

declare(strict_types=1);

namespace App\Http\Controllers\Posts\V1;

use App\Actions\Posts\StorePostAction;
use App\Http\Requests\Posts\V1\StoreRequest;
use App\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class StoreController
{
    public function __construct(
        private readonly StorePostAction $action,
    ) {}

    #[OA\Post(
        path: '/v1/posts',
        operationId: 'postsStore',
        summary: 'Create post',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/PostStoreRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Post created',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Success'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/Post'),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
        ]
    )]
    public function __invoke(StoreRequest $request): JsonResponse
    {
        $post = $this->action->handle(
            payload: $request->payload(),
        );

        return new JsonResponse(
            data: [
                'status' => true,
                'message' => 'Success',
                'data' => new PostResource($post),
            ],
            status: Response::HTTP_CREATED,
        );
    }
}
