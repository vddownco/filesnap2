<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\User;

use App\Application\Domain\Entity\User\Repository\UserRepositoryInterface;
use App\Application\Domain\Entity\User\Service\PasswordHasherInterface;
use App\Application\Domain\Entity\User\User;
use App\Application\Domain\Entity\User\UserRole;
use App\Application\UseCase\User\Create\CreateUserRequest;
use App\Application\UseCase\User\Create\CreateUserUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Uid\Uuid;

final class CreateTest extends FilesnapTestCase
{
    public static function ItCreateProvider(): array
    {
        return [
            [[UserRole::User]],
            [[UserRole::User, UserRole::Admin]],
        ];
    }

    /**
     * @throws Exception
     */
    #[DataProvider('itCreateProvider')]
    public function testItCreateUser(array $userRoles): void
    {
        $request = new CreateUserRequest(
            email: 'email@domain.com',
            plainPassword: 'plainPassword123',
            roles: $userRoles,
        );

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $capturedUserId = null;

        $userRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($user) use (&$capturedUserId) {
                $isUser = $user instanceof User;

                if ($isUser === true) {
                    $capturedUserId = $user->getId();
                }

                return $isUser;
            }));

        $passwordHasherMock = $this->createMock(PasswordHasherInterface::class);

        $passwordHasherMock
            ->expects($this->once())
            ->method('hash')
            ->with($request->getPlainPassword())
            ->willReturn('this-is-a-password-hash');

        $useCase = new CreateUserUseCase($userRepositoryMock, $passwordHasherMock);
        $response = $useCase($request);
        $user = $response->getUser();

        $this->assertSame($capturedUserId, $user->getId());
        $this->assertMatchesRegularExpression('/^.+@\S+\.\S+$/', $user->getEmail());
        $this->assertSame($request->getEmail(), $user->getEmail());
        $this->assertIsString($user->getPassword());
        $this->assertContainsOnlyInstancesOf(UserRole::class, $user->getRoles());
        $this->assertSameSize($userRoles, $user->getRoles());

        foreach ($userRoles as $role) {
            $this->assertContainsEquals($role, $user->getRoles());
        }

        $this->assertInstanceOf(Uuid::class, $user->getAuthorizationKey());
    }
}
