<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth\V1;

use App\Actions\Auth\LoginUserAction;
use App\Http\Requests\Auth\V1\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class LoginController
{
    public function __construct(
        private readonly LoginUserAction $action,
    ) {}

    #[OA\Post(
        path: '/v1/auth/login',
        operationId: 'authLogin',
        summary: 'Login user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User logged in',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                    new OA\Property(property: 'token', type: 'string'),
                ])
            )
        ]
    )]
    public function __invoke(LoginRequest $request): JsonResponse
    {
        ['user' => $user, 'token' => $token] = $this->action->handle(
            payload: $request->payload(),
        );

        return new JsonResponse(
            data: [
                'user'  => new UserResource($user),
                'token' => $token,
            ],
            status: Response::HTTP_OK,
        );
    }
}
