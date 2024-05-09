<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\User;

use App\Application\Domain\Entity\User\Repository\UserRepositoryInterface;
use App\Application\UseCase\User\UpdatePasswordById\UpdateUserPasswordByIdRequest;
use App\Application\UseCase\User\UpdatePasswordById\UpdateUserPasswordByIdUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Uid\Uuid;

final class UpdateUserPasswordByIdTest extends FilesnapTestCase
{
    /**
     * @throws Exception
     */
    public function test(): void
    {
        $request = new UpdateUserPasswordByIdRequest(Uuid::v7(), 'this-is-a-hashed-password');
        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);

        $userRepositoryMock
            ->expects($this->once())
            ->method('updatePassword')
            ->with($request->getId(), $request->getHashedPassword());

        $useCase = new UpdateUserPasswordByIdUseCase($userRepositoryMock);
        $useCase($request);
    }
}
