<?php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security;

use App\Application\UseCase\User\FindOneByEmail\FindOneUserByEmailRequest;
use App\Application\UseCase\User\FindOneByEmail\FindOneUserByEmailUseCase;
use App\Application\UseCase\User\UpdatePasswordById\UpdateUserPasswordByIdRequest;
use App\Application\UseCase\User\UpdatePasswordById\UpdateUserPasswordByIdUseCase;
use App\Infrastructure\Symfony\Security\Entity\SecurityUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final readonly class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private FindOneUserByEmailUseCase $findOneUserByEmailUseCase,
        private UpdateUserPasswordByIdUseCase $updateUserPasswordByIdUseCase
    )
    {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (false === $this->supportsClass($user::class)) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SecurityUser::class === $class || is_subclass_of($class, SecurityUser::class);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $useCaseResponse = ($this->findOneUserByEmailUseCase)(new FindOneUserByEmailRequest($identifier));
        $user = $useCaseResponse->getUser();

        if (null === $user) {
            throw new UserNotFoundException();
        }

        return SecurityUser::create($user);
    }

    /**
     * This method should not block the login, that's why it does not throw anything
     * cf PasswordUpgraderInterface::upgradePassword phpdoc
     *
     * @param SecurityUser $user
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        try {
            ($this->updateUserPasswordByIdUseCase)(
                new UpdateUserPasswordByIdRequest($user->getId(), $newHashedPassword)
            );
        } catch (\Exception) {
        }
    }
}