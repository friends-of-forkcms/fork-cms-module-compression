<?php
declare(strict_types=1);

namespace Backend\Modules\Compression\Domain\CompressionSetting;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;

final class CompressionSettingRepository extends EntityRepository
{
    /**
     * We don't flush here, see http://disq.us/p/okjc6b
     * @param CompressionSetting $setting
     * @throws ORMException
     */
    public function add(CompressionSetting $setting): void
    {
        $this->getEntityManager()->persist($setting);
    }

    /**
     * @param string $path
     * @return bool Record exists already
     */
    public function exists(string $path): bool
    {
        return $this->count(['path' => $path]) > 0;
    }

    /**
     * @param array $paths
     * @return int
     */
    public function deleteExceptPaths(array $paths): int
    {
        // If nothing was selected, delete all our settings
        if (empty($paths)) {
            return $this->createQueryBuilder('cs')->delete()->getQuery()->execute();
        }

        // Delete all that is not checked in the tree.
        return $this->createQueryBuilder('cs')
            ->delete()
            ->where('cs.path NOT IN (:checkedFolderPaths)')
            ->setParameter('checkedFolderPaths', $paths, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->execute();
    }
}
