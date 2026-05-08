<?php

declare(strict_types=1);

namespace App\Http\Controllers\Posts\V1;

use App\Actions\Posts\DestroyPostAction;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class DestroyController
{
    public function __construct(
        private readonly DestroyPostAction $action,
    ) {}

    #[OA\Delete(
        path: '/v1/posts/{post}',
        operationId: 'postsDestroy',
        summary: 'Delete post',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\PathParameter(name: 'post', required: true, schema: new OA\Schema(type: 'string', format: 'ulid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Post deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 404, description: 'Post not found', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
        ]
    )]
    public function __invoke(Post $post): JsonResponse
    {
        $this->action->handle(post: $post);

        return new JsonResponse(
            status: Response::HTTP_NO_CONTENT,
        );
    }
}
