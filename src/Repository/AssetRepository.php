<?php

namespace App\Repository;

use App\Entity\Asset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Asset>
 *
 * @method Asset|null find($id, $lockMode = null, $lockVersion = null)
 * @method Asset|null findOneBy(array $criteria, array $orderBy = null)
 * @method Asset[]    findAll()
 * @method Asset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    public function save(Asset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Asset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * getCount
     *
     * @param  mixed $decommissioned
     * @return int
     */
    public function getCount(bool $decommissioned = false): int
    {
        if ($decommissioned) {
            return $this->createQueryBuilder('asset')
                ->select('count(asset.id)')
                ->where('decommissioned = true')
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        try {
            return $this->createQueryBuilder('asset')
                ->select('count(asset.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
        }
    }

    /**
     * getDecommissionedCount
     *
     * @return int
     */
    public function getDecommissionedCount(): int
    {
        return $this->getCount(true);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findAssetId(?string $assetTag = null, ?string $serialNumber = null): ?int
    {
        $result =  $this->createQueryBuilder('asset')
            ->select('asset.id')
            ->orWhere('asset.asset_tag = :assetTag')
            ->orWhere('asset.serial_number = :serialNumber')
            ->setParameter(':assetTag', $assetTag)
            ->setParameter(':serialNumber', $serialNumber)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result['id'] ?? null;
    }
}
