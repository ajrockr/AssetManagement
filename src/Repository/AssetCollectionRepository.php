<?php

namespace App\Repository;

use App\Entity\AssetCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\NoReturn;

/**
 * @extends ServiceEntityRepository<AssetCollection>
 *
 * @method AssetCollection|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssetCollection|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssetCollection[]    findAll()
 * @method AssetCollection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetCollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetCollection::class);
    }

    public function save(AssetCollection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AssetCollection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * getCollectedAssetSlots
     *
     * @return array
     */
    public function getCollectedAssetSlots(): array
    {
        $results =  $this->createQueryBuilder('getAllCollectedAssets')
            ->select('getAllCollectedAssets.collectionLocation')
            ->getQuery()
            ->getArrayResult()
        ;

        foreach ($results as $result) {
            $return[] = $result['collectionLocation'];
        }

        return $return ?? [];
    }

    /**
     * getAllCollectedAssets
     *
     * @param int $storageId
     * @return array
     */
    public function getAllCollectedAssetsByStorageId(int $storageId): array
    {
        return $this->createQueryBuilder('assetcollection')
            ->select('assetcollection, asset.asset_tag, asset.serial_number')
            ->where('assetcollection.collectionStorage = :storageId')
            ->leftJoin('App\Entity\Asset', 'asset', 'WITH', 'asset.id = assetcollection.DeviceID')
            ->setParameter(':storageId', $storageId)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR)
        ;
    }

    /**
     * removeCollection
     *
     * @param  mixed $locations
     * @return void
     */
    public function removeCollection(int|array $locations)
    {
        $conditions = preg_filter('/^/', 'ac.collectionLocation = ', $locations);
        $qb = $this->createQueryBuilder('assetcollection');
        $delete = $qb->delete()
            ->where(
                $qb->expr()->orX()->addMultiple($conditions)
            )
            ->getQuery()
            ->execute();
    }

    /**
     * getCount
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->createQueryBuilder('assetcollection')
            ->select('count(assetcollection.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findCollectedAssetsByStorageId(int $storageId): array
    {
        return $this->createQueryBuilder('assetcollection')
            ->select('assetcollection')
            ->where('assetcollection.collectionStorage = :storageId')
            ->setParameter(':storageId', $storageId)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findOobCollectedAssetsByStorageId(int $storageId): array
    {
        return $this->createQueryBuilder('assetcollection')
            ->select('assetcollection')
            ->where('assetcollection.collectionStorage = :storageId')
            ->andWhere('assetcollection.collectionLocation IS NULL')
            ->setParameter(':storageId', $storageId)
            ->getQuery()
            ->getArrayResult()
            ;
    }
}
