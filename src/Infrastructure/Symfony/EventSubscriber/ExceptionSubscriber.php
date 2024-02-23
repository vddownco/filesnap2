<?php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\EventSubscriber;

use App\Application\Domain\Entity\Snap\Exception\FileSizeTooBigException;
use App\Application\Domain\Entity\Snap\Exception\SnapNotFoundException;
use App\Application\Domain\Entity\Snap\Exception\UnsupportedFileTypeException;
use App\Application\Domain\Exception\DomainException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['convertDomainExceptionToHttpException', -127],
            ],
        ];
    }

    public function convertDomainExceptionToHttpException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if (false === ($throwable instanceof DomainException)) {
            return;
        }

        $statusCode = match ($throwable::class) {
            SnapNotFoundException::class => Response::HTTP_NOT_FOUND,
            UnsupportedFileTypeException::class, FileSizeTooBigException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
            default => Response::HTTP_INTERNAL_SERVER_ERROR
        };

        $event->setThrowable(new HttpException($statusCode, $throwable->getMessage(), $throwable));
    }
}