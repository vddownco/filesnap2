<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\Software\SoftwareConfiguration;

use App\Infrastructure\Symfony\Security\Entity\SecurityUser;

interface SoftwareConfigurationInterface
{
    public function getConfigurationFile(SecurityUser $user): \SplFileInfo;
}
