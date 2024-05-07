<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\Snap;

use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;
use App\Application\UseCase\Snap\UpdateLastSeenDate\UpdateSnapLastSeenDateRequest;
use App\Application\UseCase\Snap\UpdateLastSeenDate\UpdateSnapLastSeenDateUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\MockObject\Exception;
use Random\RandomException;
use Symfony\Component\Uid\Uuid;

final class UpdateLastSeenDateTest extends FilesnapTestCase
{
    /**
     * @throws RandomException
     * @throws Exception
     */
    public function test(): void
    {
        $request = new UpdateSnapLastSeenDateRequest(Uuid::v4(), self::getRandomDateTime());

        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('updateLastSeenDate')
            ->with($request->getId(), $request->getLastSeenDate());

        $useCase = new UpdateSnapLastSeenDateUseCase($snapRepositoryMock);
        $useCase($request);
    }
}
