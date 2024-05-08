<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\Snap;

use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;
use App\Application\UseCase\Snap\CountByUser\CountSnapsByUserRequest;
use App\Application\UseCase\Snap\CountByUser\CountSnapsByUserUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\MockObject\Exception;
use Random\RandomException;
use Symfony\Component\Uid\Uuid;

final class CountByUserTest extends FilesnapTestCase
{
    /**
     * @throws Exception
     * @throws RandomException
     */
    public function testItCountsSnapsByUser(): void
    {
        $request = new CountSnapsByUserRequest(Uuid::v7());
        $countValue = self::getRandomInt();

        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('countByUser')
            ->with($request->getUserId())
            ->willReturn($countValue);

        $useCase = new CountSnapsByUserUseCase($snapRepositoryMock);
        $response = $useCase($request);

        $this->assertSame($countValue, $response->getCount());
    }
}
