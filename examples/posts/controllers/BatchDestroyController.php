<?php

declare(strict_types=1);

namespace App\Http\Controllers\Posts\V1;

use App\Actions\Posts\BatchDestroyAction;
use App\Http\Requests\Posts\V1\BatchDestroyRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class BatchDestroyController
{
    public function __construct(
        private readonly BatchDestroyAction $action,
    ) {}

    #[OA\Delete(
        path: '/v1/posts',
        operationId: 'postsBatchDestroy',
        summary: 'Batch delete posts',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['ids'],
                properties: [
                    new OA\Property(property: 'ids', type: 'array', items: new OA\Items(type: 'string', format: 'ulid')),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Posts deleted',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Success'),
                    new OA\Property(property: 'data', properties: [
                        new OA\Property(property: 'deleted_ids', type: 'array', items: new OA\Items(type: 'string', format: 'ulid')),
                    ]),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
        ]
    )]
    public function __invoke(BatchDestroyRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $deletedIds = $this->action->handle(
            payload: $request->payload(),
            user: $user,
        );

        return new JsonResponse(
            data: [
                'status' => true,
                'message' => 'Success',
                'data' => [
                    'deleted_ids' => $deletedIds,
                ],
            ],
            status: Response::HTTP_OK,
        );
    }
}
