<?php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final readonly class RequestSubscriber implements EventSubscriberInterface
{
    public function __construct(private Environment $twig)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['addTwigGlobalOpenMenu', 10]
            ],
        ];
    }

    public function addTwigGlobalOpenMenu(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (null !== $route && str_starts_with($route, 'client_user_')) {
            $this->twig->addGlobal('open_menu', $request->get('open_menu') ? true : null);
        }
    }
}
