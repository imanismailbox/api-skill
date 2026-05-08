<?php

declare(strict_types=1);

namespace App\Http\Controllers\Posts\V1;

use App\Attributes\QueryParameters;
use App\Concerns\HandlesApiRequest;
use App\Http\Resources\PostResource;
use App\Query\Definitions\PostQueryDefinition;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class IndexController
{
    use HandlesApiRequest;

    protected string $resource = PostResource::class;

    #[QueryParameters(
        path: '/v1/posts',
        operationId: 'postsIndex',
        summary: 'List posts',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Posts retrieved',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'status', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Success'),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Post')),
                ])
            ),
            new OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
        ],
        definition: PostQueryDefinition::class,
    )]
    public function __invoke(Request $request): Response
    {
        return $this->handleIndex(PostQueryDefinition::class, $request);
    }
}
