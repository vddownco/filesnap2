<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\ArgumentResolver;

use App\Infrastructure\Symfony\Attribute\MapUuidFromBase58;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

final readonly class UuidFromBase58ValueResolver implements ValueResolverInterface
{
    /**
     * @return array{0?:Uuid}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        $attribute = $argument->getAttributesOfType(MapUuidFromBase58::class)[0] ?? null;

        if ($attribute === null) {
            return [];
        }

        $argumentName = $attribute->name ?? $argument->getName();
        $originalValue = $request->attributes->get($argumentName);

        if (is_string($originalValue) === false) {
            throw new NotFoundHttpException(sprintf('Invalid argument %s, it must be a string.', $argumentName));
        }

        try {
            $uuid = Uuid::fromBase58($originalValue);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException(sprintf('Invalid base 58 uuid "%s"', $originalValue));
        }

        return [$uuid];
    }
}
