<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\User\Ajax;

use App\Application\UseCase\User\UpdateAuthorizationKeyById\UpdateUserAuthorizationKeyByIdRequest;
use App\Application\UseCase\User\UpdateAuthorizationKeyById\UpdateUserAuthorizationKeyByIdUseCase;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/u/apikey/reset',
    name: 'client_user_ajax_apikey_reset',
    methods: Request::METHOD_POST,
    format: 'json'
)]
final class ApiKeyResetController extends FilesnapAbstractController
{
    public function __invoke(
        UpdateUserAuthorizationKeyByIdUseCase $updateUserAuthorizationKeyByIdUseCase
    ): JsonResponse {
        $useCaseResponse = $updateUserAuthorizationKeyByIdUseCase(new UpdateUserAuthorizationKeyByIdRequest(
            $this->getAuthenticatedUser()->getId()
        ));

        return $this->json(['apikey' => $useCaseResponse->getAuthorizationKey()->toBase58()]);
    }
}
