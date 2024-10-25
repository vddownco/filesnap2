<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security;

use App\Infrastructure\Symfony\Security\Entity\SecurityUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final readonly class AuthenticationService
{
    /**
     * @param UserProviderInterface<SecurityUser> $userProvider
     */
    public function __construct(
        private Security $security,
        private UserProviderInterface $userProvider,
    ) {
    }

    public function login(string $identifier): void
    {
        $this->security->login($this->userProvider->loadUserByIdentifier($identifier));
    }
}
