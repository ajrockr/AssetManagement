<?php

namespace App\Repository;

use App\Entity\SiteConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteConfig>
 *
 * @method SiteConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method SiteConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method SiteConfig[]    findAll()
 * @method SiteConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteConfig::class);
    }

    public function save(SiteConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SiteConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByName($value): mixed
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.configName = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getAllConfigItems(): array
    {
        $results = $this->createQueryBuilder('c')
            ->select('c.configName', 'c.configValue')
            ->getQuery()
            ->getScalarResult()
        ;

        $return = [];
        foreach($results as $configArray) {
            $return[$configArray['configName']] = $configArray['configValue'];
        }

        return $return;
    }
}
