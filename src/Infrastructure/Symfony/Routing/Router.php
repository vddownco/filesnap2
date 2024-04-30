<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Routing;

use App\Infrastructure\Symfony\Attribute\MapUuidFromBase58;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Uid\Uuid;

#[AsDecorator('router.default')]
class Router implements RouterInterface, WarmableInterface
{
    private static array $controllersUuidMapParameters = [];

    public function __construct(
        #[AutowireDecorated] private readonly RouterInterface $decoratedRouter,
        #[AutowireDecorated] private readonly WarmableInterface $decoratedWarmable
    ) {
    }

    public function setContext(RequestContext $context): void
    {
        $this->decoratedRouter->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->decoratedRouter->getContext();
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->decoratedRouter->getRouteCollection();
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        if (str_starts_with($name, '_')) {
            return $this->decoratedRouter->generate($name, $parameters, $referenceType);
        }

        $route = $this->getRouteCollection()->get($name);

        if ($route === null) {
            return $this->decoratedRouter->generate($name, $parameters, $referenceType);
        }

        $controllerClass = $route->getDefault('_controller');

        if ($controllerClass === null) {
            return $this->decoratedRouter->generate($name, $parameters, $referenceType);
        }

        $parametersToConvert = self::$controllersUuidMapParameters[$controllerClass] ?? null;

        if ($parametersToConvert === []) {
            return $this->decoratedRouter->generate($name, $parameters, $referenceType);
        }

        if ($parametersToConvert !== null) {
            return $this->decoratedRouter->generate(
                $name,
                $this->getConvertedParameters($parameters, $parametersToConvert),
                $referenceType
            );
        }

        try {
            $invokeMethod = (new \ReflectionClass($controllerClass))->getMethod('__invoke');
        } catch (\ReflectionException) {
            return $this->decoratedRouter->generate($name, $parameters, $referenceType);
        }

        $parametersWithUuidMapping = array_filter(
            $invokeMethod->getParameters(),
            static fn (\ReflectionParameter $parameter) => $parameter->getAttributes(MapUuidFromBase58::class) !== []
        );

        $parametersToConvert = array_map(
            static fn (\ReflectionParameter $parameter) => $parameter->name,
            $parametersWithUuidMapping
        );

        self::$controllersUuidMapParameters[$controllerClass] = $parametersToConvert;

        if ($parametersToConvert === []) {
            return $this->decoratedRouter->generate($name, $parameters, $referenceType);
        }

        return $this->decoratedRouter->generate(
            $name,
            $this->getConvertedParameters($parameters, $parametersToConvert),
            $referenceType
        );
    }

    public function match(string $pathinfo): array
    {
        return $this->decoratedRouter->match($pathinfo);
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        return $this->decoratedWarmable->warmUp($cacheDir, $buildDir);
    }

    private function getConvertedParameters(array $parameters, array $parametersToConvert): array
    {
        $convertedParameters = [];

        foreach ($parameters as $parameterName => $parameterValue) {
            if (in_array($parameterName, $parametersToConvert, true) === false) {
                $convertedParameters[$parameterName] = $parameterValue;
                continue;
            }

            if (false === ($parameterValue instanceof Uuid)) {
                throw new \RuntimeException("Value $parameterValue for parameter $parameterName is not a valid uuid.");
            }

            $convertedParameters[$parameterName] = $parameterValue->toBase58();
        }

        return $convertedParameters;
    }
}
