<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\User;

use App\Application\Domain\User\Service\PasswordHasherInterface;
use App\Application\Domain\User\UserRepositoryInterface;
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
    public function testAlreadyHashed(): void
    {
        $request = new UpdateUserPasswordByIdRequest(
            Uuid::v7(),
            'this-is-a-hashed-password',
            true
        );

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);

        $userRepositoryMock
            ->expects($this->once())
            ->method('updatePassword')
            ->with($request->getId(), $request->getPassword());

        $passwordHasherMock = $this->createMock(PasswordHasherInterface::class);

        $passwordHasherMock
            ->expects($this->never())
            ->method('hash');

        $useCase = new UpdateUserPasswordByIdUseCase($userRepositoryMock, $passwordHasherMock);
        $useCase($request);
    }

    /**
     * @throws Exception
     */
    public function testNotHashed(): void
    {
        $request = new UpdateUserPasswordByIdRequest(
            Uuid::v7(),
            'this-is-a-plain-password',
            false
        );

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);

        $userRepositoryMock
            ->expects($this->once())
            ->method('updatePassword')
            ->with($request->getId(), 'the-hashed-password');

        $passwordHasherMock = $this->createMock(PasswordHasherInterface::class);

        $passwordHasherMock
            ->expects($this->once())
            ->method('hash')
            ->with($request->getPassword())
            ->willReturn('the-hashed-password');

        $useCase = new UpdateUserPasswordByIdUseCase($userRepositoryMock, $passwordHasherMock);
        $useCase($request);
    }
}
