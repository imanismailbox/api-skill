<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\V1;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Requests\Auth\V1\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class RegisterController
{
    public function __construct(
        private readonly RegisterUserAction $action,
    ) {}

    #[OA\Post(
        path: '/v1/auth/register',
        operationId: 'authRegister',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                    new OA\Property(property: 'token', type: 'string'),
                ])
            )
        ]
    )]
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        ['user' => $user, 'token' => $token] = $this->action->handle(
            payload: $request->payload(),
        );

        return new JsonResponse(
            data: [
                'user'  => new UserResource($user),
                'token' => $token,
            ],
            status: Response::HTTP_CREATED,
        );
    }
}
