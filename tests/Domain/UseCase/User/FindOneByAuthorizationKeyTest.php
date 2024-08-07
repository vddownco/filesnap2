<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\User;

use App\Application\Domain\Entity\User\Repository\UserRepositoryInterface;
use App\Application\Domain\Entity\User\User;
use App\Application\Domain\Entity\User\UserRole;
use App\Application\UseCase\User\FindOneByAuthorizationKey\FindOneUserByAuthorizationKeyRequest;
use App\Application\UseCase\User\FindOneByAuthorizationKey\FindOneUserByAuthorizationKeyUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Uid\Uuid;

final class FindOneByAuthorizationKeyTest extends FilesnapTestCase
{
    /**
     * @return list<array{0:User}>
     */
    public static function provider(): array
    {
        $email = 'user@example.com';
        $password = 'this-is-a-hashed-password';

        return [
            [
                new User(
                    id: Uuid::v4(),
                    email: $email,
                    password: $password,
                    roles: [UserRole::User],
                    authorizationKey: Uuid::v4()
                ),
            ],
            [
                new User(
                    id: Uuid::v4(),
                    email: $email,
                    password: $password,
                    roles: [UserRole::User, UserRole::Admin],
                    authorizationKey: Uuid::v4()
                ),
            ],
        ];
    }

    /**
     * @throws Exception
     */
    #[DataProvider('provider')]
    public function test(User $expectedUser): void
    {
        $request = new FindOneUserByAuthorizationKeyRequest($expectedUser->getAuthorizationKey());

        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);

        $userRepositoryMock
            ->expects($this->once())
            ->method('findOneByAuthorizationKey')
            ->with($request->getAuthorizationKey())
            ->willReturn($expectedUser);

        $useCase = new FindOneUserByAuthorizationKeyUseCase($userRepositoryMock);
        $response = $useCase($request);
        $user = $response->getUser();

        $this->assertNotNull($user);
        $this->assertSame($expectedUser->getId(), $user->getId());
        $this->assertSame($expectedUser->getEmail(), $user->getEmail());
        $this->assertSame($expectedUser->getPassword(), $user->getPassword());
        $this->assertSameSize($expectedUser->getRoles(), $user->getRoles());
        $this->assertContainsOnlyInstancesOf(UserRole::class, $user->getRoles());

        foreach ($expectedUser->getRoles() as $role) {
            $this->assertContainsEquals($role, $user->getRoles());
        }

        $this->assertSame($expectedUser->getAuthorizationKey(), $user->getAuthorizationKey());
    }
}
