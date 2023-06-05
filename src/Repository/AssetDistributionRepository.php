<?php

namespace App\Repository;

use App\Entity\AssetDistribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AssetDistribution>
 *
 * @method AssetDistribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssetDistribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssetDistribution[]    findAll()
 * @method AssetDistribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetDistributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetDistribution::class);
    }

    public function save(AssetDistribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AssetDistribution $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getDistributions()
    {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->orderBy('d.createdAt', 'ASC')
            ->where('d.distributedBy is NULL')
            ->getQuery()
            ->getResult()
        ;
    }
    public function getAllDistributions()
    {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->orderBy('d.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
