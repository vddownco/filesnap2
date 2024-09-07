<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final readonly class RequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['addTwigGlobalOpenMenu', 10],
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function addTwigGlobalOpenMenu(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (is_string($route) === false) {
            return;
        }

        if (str_starts_with($route, 'client_user_')) {
            $openMenu = $request->get('open_menu') === '1' ? true : null;
            $this->twig->addGlobal('open_menu', $openMenu);
        }
    }
}
