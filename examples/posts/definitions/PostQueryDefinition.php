<?php

declare(strict_types=1);

namespace App\Query\Definitions;

use App\Models\Post;
use App\Query\Attributes\QueryDefinition;

#[QueryDefinition(
    paginatePerpage: 15,
    allowFilter: ['status', 'user_id'],
    allowSort: ['created_at', 'title'],
    defaultSort: '-created_at',
    searchable: ['title', 'content']
)]
final class PostQueryDefinition extends BaseQueryDefinition
{
    public static function model(): string
    {
        return Post::class;
    }
}
