<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\Snap;

use App\Application\Domain\Entity\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Entity\Snap\Factory\SnapFactory;
use App\Application\Domain\Entity\Snap\FileStorage\File;
use App\Application\Domain\Entity\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Entity\Snap\MimeType;
use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;
use App\Application\Domain\Entity\Snap\Snap;
use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdRequest;
use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use Random\RandomException;
use Symfony\Component\Uid\Uuid;

final class FindOneByIdTest extends FilesnapTestCase
{
    /**
     * @throws Exception
     * @throws FileNotFoundException
     * @throws RandomException
     */
    public static function provider(): array
    {
        $fileRepositoryStub = self::createConfiguredStub(FileStorageInterface::class, [
            'get' => new File('/this/is/an/absolute/path/to/file.jpg'),
        ]);

        $snapFactory = new SnapFactory($fileRepositoryStub);

        $expectedSnap = $snapFactory->create(
            id: Uuid::v4(),
            userId: Uuid::v7(),
            originalFilename: 'original-filename.jpg',
            mimeType: MimeType::ImageJpeg,
            creationDate: self::getRandomDateTime(),
            lastSeenDate: self::getRandomDateTime()
        );

        return [
            [$expectedSnap->getId(), $expectedSnap],
            [Uuid::v4(), null],
        ];
    }

    /**
     * @throws Exception
     */
    #[DataProvider('provider')]
    public function test(Uuid $id, ?Snap $expectedSnap)
    {
        $request = new FindOneSnapByIdRequest($id);

        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('findOneById')
            ->with($request->getId())
            ->willReturn($expectedSnap);

        $useCase = new FindOneSnapByIdUseCase($snapRepositoryMock);

        $response = $useCase($request);
        $actualSnap = $response->getSnap();

        if ($expectedSnap === null) {
            $this->assertNull($actualSnap);
        } else {
            $this->assertEquals($expectedSnap->getId(), $actualSnap->getId());
            $this->assertEquals($expectedSnap->getUserId(), $actualSnap->getUserId());
            $this->assertEquals($expectedSnap->getOriginalFilename(), $actualSnap->getOriginalFilename());
            $this->assertEquals($expectedSnap->getMimeType(), $actualSnap->getMimeType());
            $this->assertEquals($expectedSnap->getCreationDate(), $actualSnap->getCreationDate());
            $this->assertEquals($expectedSnap->getLastSeenDate(), $actualSnap->getLastSeenDate());
            $this->assertEquals($expectedSnap->getFile()->getAbsolutePath(), $actualSnap->getFile()->getAbsolutePath());
        }
    }
}
