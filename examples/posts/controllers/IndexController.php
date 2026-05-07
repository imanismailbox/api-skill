<?php

declare(strict_types=1);

namespace App\Http\Controllers\Posts\V1;

use App\Attributes\QueryParameters;
use App\Concerns\HandlesApiRequest;
use App\Http\Resources\PostResource;
use App\Query\Definitions\PostQueryDefinition;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class IndexController
{
    use HandlesApiRequest;

    protected string $resource = PostResource::class;

    #[QueryParameters(
        path: '/v1/posts',
        operationId: 'postsIndex',
        summary: 'List posts',
        definition: PostQueryDefinition::class,
    )]
    public function __invoke(Request $request): Response
    {
        return $this->handleIndex(PostQueryDefinition::class, $request);
    }
}
