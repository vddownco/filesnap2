<?php

declare(strict_types=1);

namespace App\Infrastructure\Entity\Snap\Repository;

use App\Application\Domain\Entity\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Entity\Snap\Exception\UnsupportedFileTypeException;
use App\Application\Domain\Entity\Snap\Factory\SnapFactory;
use App\Application\Domain\Entity\Snap\MimeType;
use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;
use App\Application\Domain\Entity\Snap\Snap;
use App\Infrastructure\Entity\MariadbTools;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-type DbResult array{
 *      id:string,
 *      user_id:string,
 *      original_filename:string,
 *      mime_type:string,
 *      creation_date:string,
 *      last_seen_date:string|null
 *  }
 */
final readonly class MariadbSnapRepository implements SnapRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private SnapFactory $snapFactory
    ) {
    }

    /**
     * @throws Exception
     */
    public function create(Snap $snap): void
    {
        $query = '
            INSERT INTO snap (id, user_id, original_filename, mime_type, creation_date, last_seen_date)
            VALUES (:id, :user_id, :original_filename, :mime_type, :creation_date, :last_seen_date)
        ';

        $this->connection->executeQuery($query, [
            'id' => $snap->getId()->toRfc4122(),
            'user_id' => $snap->getUserId()->toRfc4122(),
            'original_filename' => $snap->getOriginalFilename(),
            'mime_type' => $snap->getMimeType()->value,
            'creation_date' => $snap->getCreationDate()->format(MariadbTools::DATETIME_FORMAT),
            'last_seen_date' => $snap->getLastSeenDate()?->format(MariadbTools::DATETIME_FORMAT),
        ]);
    }

    /**
     * @throws Exception
     */
    public function updateLastSeenDate(Uuid $id, \DateTimeInterface $lastSeenDate): void
    {
        $query = 'UPDATE snap SET last_seen_date = :last_seen_date WHERE id = :id';

        $this->connection->executeQuery($query, [
            'id' => $id->toRfc4122(),
            'last_seen_date' => $lastSeenDate->format(MariadbTools::DATETIME_FORMAT),
        ]);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function findOneById(Uuid $id): ?Snap
    {
        $query = '
            SELECT id, user_id, original_filename, mime_type, creation_date, last_seen_date
            FROM snap
            WHERE id = :id
        ';

        /** @var DbResult|false $dbResult */
        $dbResult = $this->connection->fetchAssociative($query, ['id' => $id->toRfc4122()]);

        if ($dbResult === false) {
            return null;
        }

        return $this->createSnapEntity($dbResult);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function findByIds(array $ids): array
    {
        $query = '
            SELECT id, user_id, original_filename, mime_type, creation_date, last_seen_date
            FROM snap
            WHERE id IN (:ids)
        ';

        /** @var list<DbResult> $dbResults */
        $dbResults = $this->connection->fetchAllAssociative(
            $query,
            ['ids' => array_map(
                static fn (Uuid $id) => $id->toRfc4122(),
                $ids
            )],
            ['ids' => ArrayParameterType::STRING]
        );

        return array_map(
            fn (array $dbResult) => $this->createSnapEntity($dbResult),
            $dbResults
        );
    }

    /**
     * @return Snap[]
     *
     * @throws Exception
     * @throws \Exception
     */
    public function findByUser(Uuid $userId, int $offset, int $limit): array
    {
        $query = '
            SELECT id, user_id, original_filename, mime_type, creation_date, last_seen_date
            FROM snap
            WHERE user_id = :user_id
            ORDER BY creation_date DESC
            LIMIT :limit
            OFFSET :offset
        ';

        /** @var list<DbResult> $dbResults */
        $dbResults = $this->connection->fetchAllAssociative(
            $query,
            [
                'user_id' => $userId->toRfc4122(),
                'limit' => $limit,
                'offset' => $offset,
            ],
            [
                'user_id' => ParameterType::STRING,
                'limit' => ParameterType::INTEGER,
                'offset' => ParameterType::INTEGER,
            ]
        );

        return array_map(
            fn (array $dbResult) => $this->createSnapEntity($dbResult),
            $dbResults
        );
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function countByUser(Uuid $userId): int
    {
        $query = 'SELECT COUNT(id) FROM snap WHERE user_id = :user_id';
        $count = $this->connection->fetchOne($query, ['user_id' => $userId->toRfc4122()]);

        if (is_int($count) === false) {
            throw new \Exception(__FUNCTION__ . ' doctrine returns is not a correct count value.');
        }

        return $count;
    }

    /**
     * @throws Exception
     */
    public function deleteOneById(Uuid $id): void
    {
        $query = 'DELETE FROM snap WHERE id = :id';

        $this->connection->executeQuery($query, ['id' => $id->toRfc4122()]);
    }

    /**
     * @throws Exception
     */
    public function deleteByIds(Uuid $userId, array $ids): void
    {
        $query = 'DELETE FROM snap WHERE user_id = :user_id AND id IN (:ids)';

        $this->connection->executeQuery(
            $query,
            [
                'user_id' => $userId->toRfc4122(),
                'ids' => array_map(
                    static fn (Uuid $id) => $id->toRfc4122(),
                    $ids
                ),
            ],
            [
                'user_id' => ParameterType::STRING,
                'ids' => ArrayParameterType::STRING,
            ]
        );
    }

    /**
     * @param DbResult $dbResult
     *
     * @throws FileNotFoundException
     * @throws UnsupportedFileTypeException
     */
    private function createSnapEntity(array $dbResult): Snap
    {
        return $this->snapFactory->create(
            id: Uuid::fromString($dbResult['id']),
            userId: Uuid::fromString($dbResult['user_id']),
            originalFilename: $dbResult['original_filename'],
            mimeType: MimeType::fromString($dbResult['mime_type']),
            creationDate: new \DateTime($dbResult['creation_date']),
            lastSeenDate: $dbResult['last_seen_date'] !== null ? new \DateTime($dbResult['last_seen_date']) : null
        );
    }
}
