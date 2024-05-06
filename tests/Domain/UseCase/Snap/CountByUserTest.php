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
        $countValue = self::getRandomInt();

        $snapRepositoryStub = $this->createConfiguredStub(SnapRepositoryInterface::class, [
            'countByUser' => $countValue,
        ]);

        $useCase = new CountSnapsByUserUseCase($snapRepositoryStub);
        $response = $useCase(new CountSnapsByUserRequest(Uuid::v7()));

        $this->assertEquals($countValue, $response->getCount());
    }
}
