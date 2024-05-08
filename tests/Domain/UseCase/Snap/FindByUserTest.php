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
use App\Application\UseCase\Snap\FindByUser\FindSnapsByUserRequest;
use App\Application\UseCase\Snap\FindByUser\FindSnapsByUserUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use Random\RandomException;
use Symfony\Component\Uid\Uuid;

final class FindByUserTest extends FilesnapTestCase
{
    /**
     * @return Snap[]
     *
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

        $snaps = [];
        for ($i = 0; $i < 5; ++$i) {
            $snaps[] = $snapFactory->create(
                id: Uuid::v4(),
                userId: Uuid::v7(),
                originalFilename: 'original-filename.jpg',
                mimeType: MimeType::ImageJpeg,
                creationDate: self::getRandomDateTime(),
                lastSeenDate: self::getRandomDateTime()
            );
        }

        return [
            [$snaps],
            [[]],
        ];
    }

    /**
     * @param Snap[] $expectedSnaps
     *
     * @throws Exception
     * @throws RandomException
     */
    #[DataProvider('provider')]
    public function test(array $expectedSnaps)
    {
        $request = new FindSnapsByUserRequest(Uuid::v7(), self::getRandomInt(), self::getRandomInt());

        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('findByUser')
            ->with($request->getUserId(), $request->getOffset(), $request->getLimit())
            ->willReturn($expectedSnaps);

        $useCase = new FindSnapsByUserUseCase($snapRepositoryMock);

        $response = $useCase($request);
        $actualSnaps = $response->getSnaps();

        $this->assertSameSize($expectedSnaps, $actualSnaps);

        if ($expectedSnaps !== []) {
            foreach ($expectedSnaps as $i => $expectedSnap) {
                $actualSnap = $actualSnaps[$i];

                $this->assertSame($expectedSnap->getId(), $actualSnap->getId());
                $this->assertSame($expectedSnap->getUserId(), $actualSnap->getUserId());
                $this->assertSame($expectedSnap->getOriginalFilename(), $actualSnap->getOriginalFilename());
                $this->assertSame($expectedSnap->getMimeType(), $actualSnap->getMimeType());
                $this->assertSame($expectedSnap->getCreationDate(), $actualSnap->getCreationDate());
                $this->assertSame($expectedSnap->getLastSeenDate(), $actualSnap->getLastSeenDate());
                $this->assertSame($expectedSnap->getFile()->getAbsolutePath(), $actualSnap->getFile()->getAbsolutePath());
            }
        }
    }
}
