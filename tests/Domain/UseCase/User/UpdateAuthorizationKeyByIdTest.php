<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\User;

use App\Application\Domain\User\UserRepositoryInterface;
use App\Application\UseCase\User\UpdateAuthorizationKeyById\UpdateUserAuthorizationKeyByIdRequest;
use App\Application\UseCase\User\UpdateAuthorizationKeyById\UpdateUserAuthorizationKeyByIdUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Uid\Uuid;

final class UpdateAuthorizationKeyByIdTest extends FilesnapTestCase
{
    /**
     * @throws Exception
     */
    public function test(): void
    {
        $userId = Uuid::v7();

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $capturedNewAuthorizationKeyParam = null;

        $userRepositoryMock
            ->expects($this->once())
            ->method('updateAuthorizationKey')
            ->with(
                $userId,
                self::callback(
                    function ($parameter) use (&$capturedNewAuthorizationKeyParam): bool {
                        $capturedNewAuthorizationKeyParam = $parameter;

                        return $parameter instanceof Uuid;
                    }
                )
            );

        $useCase = new UpdateUserAuthorizationKeyByIdUseCase($userRepositoryMock);
        $response = $useCase(new UpdateUserAuthorizationKeyByIdRequest($userId));
        $newAuthorizationKey = $response->getAuthorizationKey();

        self::assertSame($capturedNewAuthorizationKeyParam, $newAuthorizationKey);
    }
}
