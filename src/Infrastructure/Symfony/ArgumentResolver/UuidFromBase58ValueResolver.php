<?php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\ArgumentResolver;

use App\Infrastructure\Symfony\Attribute\MapUuidFromBase58;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

final class UuidFromBase58ValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        $attribute = $argument->getAttributesOfType(MapUuidFromBase58::class)[0] ?? null;

        if (null === $attribute) {
            return [];
        }

        $originalValue = $request->attributes->get($attribute->name ?? $argument->getName());

        try {
            $uuid = Uuid::fromBase58($originalValue);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException(sprintf('Invalid base 58 uuid "%s"', $originalValue));
        }

        return [$uuid];
    }
}