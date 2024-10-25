<?php

declare(strict_types=1);

namespace App\Application\Domain\User;

use Symfony\Component\Uid\Uuid;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findOneById(Uuid $id): ?User;

    public function findOneByEmail(string $email): ?User;

    public function findOneByAuthorizationKey(Uuid $authorizationKey): ?User;

    public function updatePassword(Uuid $id, string $hashedPassword): void;

    public function updateAuthorizationKey(Uuid $id, Uuid $authorizationKey): void;

    public function updateEmail(Uuid $id, string $email): void;
}
