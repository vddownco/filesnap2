<?php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Twig;

use Composer\InstalledVersions;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProjectVersionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'project_version',
                static fn(): string => InstalledVersions::getPrettyVersion('maximethiry/filesnap')
            )
        ];
    }
}