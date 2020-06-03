<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionHistory;

use Backend\Modules\Compression\Domain\CompressionHistory\Helpers\Helper;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use PDO;

final class CompressionHistoryRepository extends EntityRepository
{
    /**
     * We don't flush here, see http://disq.us/p/okjc6b
     * @param CompressionHistory $compressionHistoryRecord
     * @throws ORMException
     */
    public function add(CompressionHistory $compressionHistoryRecord): void
    {
        $this->getEntityManager()->persist($compressionHistoryRecord);
    }

    /**
     * Fetch relevant statistics based on previous compression results.
     * @return array
     * @throws DBALException
     */
    public function getStatistics(): array
    {
        // Use a raw connection as Doctrine DQL does not support ROUND
        $connection = $this->getEntityManager()->getConnection();
        $results = $connection->executeQuery('
            SELECT
                COUNT(id) AS totalCompressedImages,
                SUM(originalSize - compressedSize) AS savedBytes,
                CONCAT(ROUND((100 - (SUM(compressedSize) / SUM(originalSize) * 100)),2),"%") AS savedPercentage
            FROM CompressionHistory
        ')->fetch(PDO::FETCH_ASSOC);

        return [
            'totalCompressedImages' => (int) $results['totalCompressedImages'],
            'savedBytes' => (int) $results['savedBytes'],
            'savedBytesFormatted' => Helper::readableBytes((int) $results['savedBytes']),
            'savedPercentage' => $results['savedPercentage'],
        ];
    }
}
