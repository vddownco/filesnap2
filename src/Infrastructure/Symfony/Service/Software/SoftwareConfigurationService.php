<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\Software;

use App\Infrastructure\Symfony\Security\Entity\SecurityUser;
use App\Infrastructure\Symfony\Service\Software\SoftwareConfiguration\SharexConfigurationService;
use Symfony\Component\HttpFoundation\File\File;

final readonly class SoftwareConfigurationService
{
    public function __construct(private SharexConfigurationService $sharexConfigurationService)
    {
    }

    /**
     * @throws \JsonException
     */
    public function getConfigurationFile(Software $software, SecurityUser $user): File
    {
        return match ($software) {
            Software::Sharex => $this->sharexConfigurationService->getConfigurationFile($user),
        };
    }
}
