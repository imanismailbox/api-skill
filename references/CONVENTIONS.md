# Conventions Reference

This document covers folder structure, naming conventions, and complete worked examples for the API skill.

---

## Folder Structure

```
app/
  Actions/
    Posts/
      StorePostAction.php
      UpdatePostAction.php
      DestroyPostAction.php
  Http/
    Controllers/
      Auth/
        V1/
          LoginController.php
          LogoutController.php
          RegisterController.php
      Posts/
        V1/
          IndexController.php
          ShowController.php
          StoreController.php
          UpdateController.php
          DestroyController.php
          BatchDestroyController.php
    Middleware/
      ForceJsonResponse.php
      Sunset.php
    Payloads/
      Posts/
        StorePayload.php
        UpdatePayload.php
      Auth/
        RegisterUserPayload.php
    Requests/
      Auth/
        V1/
          LoginRequest.php
          RegisterRequest.php
      Posts/
        V1/
          StoreRequest.php
          UpdateRequest.php
    Resources/
      PostResource.php
      UserResource.php
    Responses/
      ProblemResponse.php
  Jobs/
    Posts/
      StorePostJob.php
  Policies/
    PostPolicy.php
routes/
  api/
    routes.php
    auth.php
    posts.php
tests/
  Feature/
    Auth/
      V1/
        LoginTest.php
        RegisterTest.php
    Posts/
      V1/
        IndexTest.php
        ShowTest.php
        StoreTest.php
        UpdateTest.php
        DestroyTest.php
```

---

## Naming Conventions

| Layer | Convention | Example |
|---|---|---|
| Controller | `{Action}Controller` | `StoreController`, `DestroyController` |
| Action | `{Action}{Resource}Action` | `StorePostAction`, `UpdatePostAction` |
| Payload (DTO) | `{Action}Payload` | `StorePayload` |
| Form Request | `{Action}Request` | `StoreRequest` |
| API Resource | `{Resource}Resource` | `PostResource` |
| Job | `{Action}{Resource}Job` | `StorePostJob` |
| Route name | `{resource}:{version}:{action}` | `posts:v1:store` |
| Test file | `{Action}Test` in the matching path | `StoreTest.php` |

---

## Complete Worked Example — Listing Posts (Index)

### Query Definition

```php
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
```

### API Resource

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Post */
#[OA\Schema(
    schema: 'Post',
    required: ['id', 'title', 'content'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'ulid'),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'content', type: 'string'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
final class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

### Controller

```php
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
            new OA\\Response(
                response: 200,
                description: 'Posts retrieved',
                content: new OA\\JsonContent(properties: [
                    new OA\\Property(property: 'status', type: 'boolean', example: true),
                    new OA\\Property(property: 'message', type: 'string', example: 'Success'),
                    new OA\\Property(property: 'data', type: 'array', items: new OA\\Items(ref: '#/components/schemas/Post')),
                ])
            ),
            new OA\\Response(response: 401, description: 'Unauthorized', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
        ],
        definition: PostQueryDefinition::class,
    )]
    public function __invoke(Request $request): Response
    {
        return $this->handleIndex(PostQueryDefinition::class, $request);
    }
}
```

---

## Complete Worked Example — Showing a Post (Detail)

### Controller

```php
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
    #[OA\\Get(
        path: '/v1/posts/{post}',
        operationId: 'postsShow',
        summary: 'Show post',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\\PathParameter(name: 'post', required: true, schema: new OA\\Schema(type: 'string', format: 'ulid')),
        ],
        responses: [
            new OA\\Response(
                response: 200,
                description: 'Post retrieved',
                content: new OA\\JsonContent(properties: [
                    new OA\\Property(property: 'status', type: 'boolean', example: true),
                    new OA\\Property(property: 'message', type: 'string', example: 'Success'),
                    new OA\\Property(property: 'data', ref: '#/components/schemas/Post'),
                ])
            ),
            new OA\\Response(response: 401, description: 'Unauthorized', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
            new OA\\Response(response: 404, description: 'Post not found', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
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
```

---

## Complete Worked Example — Storing a Post (Create)

### Payload

```php
<?php

declare(strict_types=1);

namespace App\Http\Payloads\Posts;

use OpenApi\Attributes as OA;

#[OA\\Schema(
    schema: 'PostStoreRequest',
    required: ['title', 'content'],
    properties: [
        new OA\\Property(property: 'title', type: 'string', maxLength: 255),
        new OA\\Property(property: 'content', type: 'string'),
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
```

### Controller

```php
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

    #[OA\\Post(
        path: '/v1/posts',
        operationId: 'postsStore',
        summary: 'Create post',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        requestBody: new OA\\RequestBody(
            required: true,
            content: new OA\\JsonContent(ref: '#/components/schemas/PostStoreRequest')
        ),
        responses: [
            new OA\\Response(
                response: 201,
                description: 'Post created',
                content: new OA\\JsonContent(properties: [
                    new OA\\Property(property: 'status', type: 'boolean', example: true),
                    new OA\\Property(property: 'message', type: 'string', example: 'Success'),
                    new OA\\Property(property: 'data', ref: '#/components/schemas/Post'),
                ])
            ),
            new OA\\Response(response: 401, description: 'Unauthorized', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
            new OA\\Response(response: 422, description: 'Validation error', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
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
```

---

## Complete Worked Example — Updating a Post (Update)

### Payload

```php
<?php

declare(strict_types=1);

namespace App\Http\Payloads\Posts;

use OpenApi\Attributes as OA;

#[OA\\Schema(
    schema: 'PostUpdateRequest',
    properties: [
        new OA\\Property(property: 'title', type: 'string', maxLength: 255),
        new OA\\Property(property: 'content', type: 'string'),
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
```

### Controller

```php
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

    #[OA\\Put(
        path: '/v1/posts/{post}',
        operationId: 'postsUpdate',
        summary: 'Update post',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\\PathParameter(name: 'post', required: true, schema: new OA\\Schema(type: 'string', format: 'ulid')),
        ],
        requestBody: new OA\\RequestBody(
            required: true,
            content: new OA\\JsonContent(ref: '#/components/schemas/PostUpdateRequest')
        ),
        responses: [
            new OA\\Response(
                response: 200,
                description: 'Post updated',
                content: new OA\\JsonContent(properties: [
                    new OA\\Property(property: 'status', type: 'boolean', example: true),
                    new OA\\Property(property: 'message', type: 'string', example: 'Success'),
                    new OA\\Property(property: 'data', ref: '#/components/schemas/Post'),
                ])
            ),
            new OA\\Response(response: 401, description: 'Unauthorized', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
            new OA\\Response(response: 404, description: 'Post not found', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
            new OA\\Response(response: 422, description: 'Validation error', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
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
```

---

## Complete Worked Example — Deleting a Post (Delete)

### Controller

```php
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

    #[OA\\Delete(
        path: '/v1/posts/{post}',
        operationId: 'postsDestroy',
        summary: 'Delete post',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\\PathParameter(name: 'post', required: true, schema: new OA\\Schema(type: 'string', format: 'ulid')),
        ],
        responses: [
            new OA\\Response(response: 204, description: 'Post deleted successfully'),
            new OA\\Response(response: 401, description: 'Unauthorized', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
            new OA\\Response(response: 404, description: 'Post not found', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
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
```

---

## Complete Worked Example — Batch Deleting Posts (Batch Delete)

### Controller

```php
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

    #[OA\\Delete(
        path: '/v1/posts',
        operationId: 'postsBatchDestroy',
        summary: 'Batch delete posts',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        requestBody: new OA\\RequestBody(
            required: true,
            content: new OA\\JsonContent(
                required: ['ids'],
                properties: [
                    new OA\\Property(property: 'ids', type: 'array', items: new OA\\Items(type: 'string', format: 'ulid')),
                ]
            )
        ),
        responses: [
            new OA\\Response(
                response: 200,
                description: 'Posts deleted',
                content: new OA\\JsonContent(properties: [
                    new OA\\Property(property: 'status', type: 'boolean', example: true),
                    new OA\\Property(property: 'message', type: 'string', example: 'Success'),
                    new OA\\Property(property: 'data', properties: [
                        new OA\\Property(property: 'deleted_ids', type: 'array', items: new OA\\Items(type: 'string', format: 'ulid')),
                    ]),
                ])
            ),
            new OA\\Response(response: 401, description: 'Unauthorized', content: new OA\\JsonContent(ref: '#/components/schemas/Message')),
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
```

---

## Route Files

### `routes/api/routes.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::as('auth:')->group(base_path(
    path: 'routes/api/auth.php',
));

Route::as('posts:')->group(base_path(
    path: 'routes/api/posts.php',
));
```

### `routes/api/auth.php`

```php
<?php

declare(strict_types=1);

use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->middleware('throttle:api')->group(function (): void {
    Route::post('/register', Auth\\V1\\RegisterController::class)->name('v1:register');
    Route::post('/login', Auth\\V1\\LoginController::class)->name('v1:login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::delete('/logout', Auth\\V1\\LogoutController::class)->name('v1:logout');
    });
});
```

---

## Model — ULID Primary Keys

All API-facing models use `HasUlids`. The migration column must be `ulid`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

final class Post extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
```

Migration:

```php
$table->ulid('id')->primary();
```

Never use `$table->id()` (auto-increment) on a model that is exposed through an API endpoint.

---

## ProblemResponse

`app/Http/Responses/ProblemResponse.php` — implements `Responsable` so it can be returned directly from any exception handler closure. Sets `Content-Type: application/problem+json` as required by RFC 9457, and uses `array_filter` to omit the `errors` key when not present:

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProblemResponse implements Responsable
{
    public function __construct(
        private readonly string $type,
        private readonly string $title,
        private readonly int    $status,
        private readonly string $detail,
        private readonly array  $errors = [],
    ) {}

    public function toResponse($request): JsonResponse
    {
        return new JsonResponse(
            data: array_filter([
                'type'   => $this->type,
                'title'  => $this->title,
                'status' => $this->status,
                'detail' => $this->detail,
                'errors' => $this->errors ?: null,
            ]),
            status:  $this->status,
            headers: ['Content-Type' => 'application/problem+json'],
        );
    }
}
```

---

## RFC 9457 Problem Details — Exception Handler

Register this in `bootstrap/app.php`. Every exception handler closure returns a `ProblemResponse` — no raw arrays, no ad-hoc `JsonResponse` construction:

```php
use App\Http\Responses\ProblemResponse;
use Illuminate\\Auth\\Access\\AuthorizationException;
use Illuminate\\Auth\\AuthenticationException;
use Illuminate\\Database\\Eloquent\\ModelNotFoundException;
use Illuminate\\Http\\Request;
use Illuminate\\Validation\\ValidationException;
use Symfony\\Component\\HttpFoundation\\Response;

->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (ValidationException $e, Request $request): ProblemResponse {
        return new ProblemResponse(
            type:   'https://example.com/problems/validation-error',
            title:  'Validation Error',
            status: Response::HTTP_UNPROCESSABLE_ENTITY,
            detail: 'The given data was invalid.',
            errors: $e->errors(),
        );
    });

    $exceptions->render(function (AuthenticationException $e, Request $request): ProblemResponse {
        return new ProblemResponse(
            type:   'https://example.com/problems/unauthenticated',
            title:  'Unauthenticated',
            status: Response::HTTP_UNAUTHORIZED,
            detail: 'You are not authenticated.',
        );
    });

    $exceptions->render(function (AuthorizationException $e, Request $request): ProblemResponse {
        return new ProblemResponse(
            type:   'https://example.com/problems/forbidden',
            title:  'Forbidden',
            status: Response::HTTP_FORBIDDEN,
            detail: 'You are not authorised to perform this action.',
        );
    });

    $exceptions->render(function (ModelNotFoundException $e, Request $request): ProblemResponse {
        return new ProblemResponse(
            type:   'https://example.com/problems/not-found',
            title:  'Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: 'The requested resource could not be found.',
        );
    });

    $exceptions->render(function (\\\\Throwable $e, Request $request): ProblemResponse {
        return new ProblemResponse(
            type:   'https://example.com/problems/server-error',
            title:  'Server Error',
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
            detail: 'An unexpected error occurred.',
        );
    });
})
```

---

## AppServiceProvider — Boot Configuration

Both the rate limiter and the resource wrapping setting belong in `AppServiceProvider::boot()`:

```php
use Illuminate\\Cache\\RateLimiting\\Limit;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Http\\Request;
use Illuminate\\Http\Resources\\Json\\JsonResource;
use Illuminate\\Support\\Facades\\RateLimiter;

public function boot(): void
{
    Model::shouldBeStrict();

    JsonResource::withoutWrapping();

    RateLimiter::for('api', function (Request $request): Limit {
        return Limit::perMinute(60)->by(
            key: $request->user()?->id ?: $request->ip(),
        );
    });
}
```

`JsonResource::withoutWrapping()` disables the automatic `data` envelope on all API resources globally, so resources serialise consistently whether returned directly or wrapped in a `JsonResponse`.

---

## Sunset Middleware

Create `app/Http/Middleware/Sunset.php` to attach the `Sunset` header ([RFC 8594](https://www.rfc-editor.org/rfc/rfc8594)) to deprecated route groups:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\\Http\\Request;
use Symfony\\Component\\HttpFoundation\\Response;

final class Sunset
{
    public function handle(Request $request, Closure $next, string $date): Response
    {
        $response = $next($request);

        $response->headers->set(
            'Sunset',
            (new DateTimeImmutable($date))->format(DateTimeInterface::RFC7231),
        );

        return $response;
    }
}
```

Register the alias in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'sunset' => \\App\\Http\\Middleware\\Sunset::class,
    ]);
})
```

Apply to a versioned route group when a deprecation date is known. Both versions coexist in the same resource file:

```php
// routes/api/posts.php

Route::prefix('v1/posts')
    ->middleware(['auth:sanctum', 'throttle:api', 'sunset:2026-12-31'])
    ->group(function (): void {
        Route::get('/', Posts\\V1\\IndexController::class)->name('v1:index');
        // ...
    });

Route::prefix('v2/posts')
    ->middleware(['auth:sanctum', 'throttle:api'])
    ->group(function (): void {
        Route::get('/', Posts\\V2\\IndexController::class)->name('v2:index');
        // ...
    });
```

Consumers receive a `Sunset: Wed, 31 Dec 2026 00:00:00 GMT` header on every v1 response, giving them a clear migration deadline.

---

## ForceJsonResponse Middleware

`app/Http/Middleware/ForceJsonResponse.php` — ensures `$request->expectsJson()` returns `true` for all API requests, which guarantees the exception handler always returns Problem Details JSON rather than HTML:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\\Http\\Request;
use Symfony\\Component\\HttpFoundation\\Response;

final class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
```

Apply as the first entry in every route group's middleware stack so it fires before `auth:sanctum` and `throttle:api`:

```php
Route::prefix('v1/posts')
    ->middleware(['force.json', 'auth:sanctum', 'throttle:api'])
    ->group(function (): void {
        // ...
    });
```

---

## CORS Configuration

`config/cors.php` — for a standalone API, `paths` must be `['*']` (no web prefix exists):

```php
return [
    'paths'                    => ['*'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => false,
];
```

Add to `.env` per environment:

```
CORS_ALLOWED_ORIGINS=https://app.example.com,https://admin.example.com
```

`HandleCors` is part of Laravel's global middleware stack — no per-route changes are needed.

---

## Anti-patterns

Quick reference for what to avoid and why:

| Anti-pattern | Correct approach |
|---|---|
| `$table->id()` on API models | `$table->ulid('id')->primary()` + `HasUlids` trait |
| Business logic or API documentation in models | Move to an Action class under `app/Actions/`, Resources, and Payloads |
| Resourceful or multi-method controllers | One `final` invokable controller per operation |
| Returning `$model->toArray()` or raw `array` from a controller | Return an API Resource |
| `app(Foo::class)` or `resolve(Foo::class)` inside a method | Declare `private readonly Foo $foo` in the constructor |
| `DB::transaction()` Facade in an Action | Inject `DatabaseManager` and call `$this->database->transaction()` |
| `paginate()` on any list endpoint | `simplePaginate()` — no `COUNT(*)` |
| A route group without `throttle:api` | Always include `throttle:api`, including on auth routes |
| Any exception producing an HTML response | `ForceJsonResponse` middleware + full exception handler |
| A PHP file without `declare(strict_types=1)` | First statement after `<?php`, always |
| `if/elseif` chains selecting a single value | `match` expression |
| Policy or gate checks inside an Action | Authorize in `FormRequest::authorize()` only |

---

## Media Management — HandlesMediaUpload Trait

### Trait Implementation

`app/Concerns/HandlesMediaUpload.php` — standardizes media uploads using Spatie Media Library with automated resizing and metadata handling.

```php
<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Http\\UploadedFile;
use Illuminate\\Support\\Arr;
use Illuminate\\Support\\Facades\\Log;
use Spatie\\Image\\Image;
use Spatie\\MediaLibrary\\MediaCollections\\Models\\Media;
use Throwable;

trait HandlesMediaUpload
{
    protected function handleSingleMediaUpload(
        Model $model,
        ?UploadedFile $file,
        string $collectionName,
        ?array $captions = [],
        ?string $disk = null,
        ?array $resizeConfig = null,
    ): ?Media {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        $this->removeOldMedia(model: $model, collectionName: $collectionName);

        $mediaAdder = $model->addMedia($file);
        $mediaAdder->withCustomProperties($captions ?? []);

        $media = $disk !== null
            ? $mediaAdder->toMediaCollection(collectionName: $collectionName, diskName: $disk)
            : $mediaAdder->toMediaCollection(collectionName: $collectionName);

        $width = $resizeConfig['width'] ?? null;
        $height = $resizeConfig['height'] ?? null;

        if ($media instanceof Media && ($width !== null || $height !== null) && str_starts_with(haystack: $media->mime_type ?? '', needle: 'image/')) {
            $this->replaceOriginalWithResizedImage(media: $media, width: $width, height: $height);
        }

        return $media;
    }

    protected function handleMultipleMediaUpload(
        Model $model,
        array $files,
        string $collectionName,
        ?array $captions = [],
        ?string $disk = null,
        ?array $resizeConfig = null,
    ): array {
        $uploadedMedia = [];

        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $caption = $captions[$index] ?? null;
            $mediaAdder = $model->addMedia($file);
            $mediaAdder->withCustomProperties(['caption' => $caption]);

            $media = $disk !== null
                ? $mediaAdder->toMediaCollection(collectionName: $collectionName, diskName: $disk)
                : $mediaAdder->toMediaCollection(collectionName: $collectionName);

            $width = $resizeConfig['width'] ?? null;
            $height = $resizeConfig['height'] ?? null;

            if ($media instanceof Media && ($width !== null || $height !== null) && str_starts_with(haystack: $media->mime_type ?? '', needle: 'image/')) {
                $this->replaceOriginalWithResizedImage(media: $media, width: $width, height: $height);
            }

            $uploadedMedia[] = $media;
        }

        return $uploadedMedia;
    }

    protected function replaceOriginalWithResizedImage(Media $media, ?int $width = null, ?int $height = null): void
    {
        if ($width === null && $height === null) {
            return;
        }

        try {
            $imageProcessor = Image::load($media->getPath());

            match (true) {
                $width !== null && $height !== null => $imageProcessor->resize(width: $width, height: $height),
                $width !== null => $imageProcessor->width(width: $width),
                $height !== null => $imageProcessor->height(height: $height),
                default => null,
            };

            $imageProcessor->save();
        } catch (Throwable $e) {
            Log::error(message: "Failed to resize and replace media ID {$media->id}: {$e->getMessage()}");
        }
    }

    protected function removeOldMedia(Model $model, string $collectionName): void
    {
        $model->clearMediaCollection(collectionName: $collectionName);
    }
}
```

### Action Usage (Multiple Files)

```php
final class StoreGalleryAction
{
    use HandlesMediaUpload;

    public function handle(GalleryPayload $payload): Gallery
    {
        $gallery = Gallery::query()->create($payload->toArray());

        if (! empty($payload->images)) {
            $this->handleMultipleMediaUpload(
                model: $gallery,
                files: $payload->images,
                collectionName: 'galleries',
                captions: $payload->captions,
                resizeConfig: ['width' => 1200],
            );
        }

        return $gallery;
    }
}
```

---

## Testing Conventions

- Test files mirror the controller structure under `tests/Feature/` (e.g. `tests/Feature/Posts/V1/StoreTest.php`).
- Use Pest PHP.
- Every endpoint has at minimum: one happy path test and one unhappy path test.
- Assert on the HTTP status code and the JSON structure.
- Use `actingAs()` with a factory-created user for authenticated endpoints.
- Assert Problem Details shape on error responses.

```php
uses(RefreshDatabase::class);

it('stores a post and returns 201', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/v1/posts', [
            'title'   => 'Hello World',
            'content' => 'Body text.',
        ])
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.title', 'Hello World');
});

it('returns problem details when title is missing', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/v1/posts', ['content' => 'Body text.'])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('status', 422)
        ->assertJsonPath('title', 'Validation Error')
        ->assertJsonStructure(['type', 'title', 'status', 'detail', 'errors']);
});

it('returns 401 when unauthenticated', function (): void {
    $this->postJson('/v1/posts', [])
        ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonPath('title', 'Unauthenticated');
});
```
