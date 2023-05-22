<?php

namespace App\Repository;

use App\Entity\SiteView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteView>
 *
 * @method SiteView|null find($id, $lockMode = null, $lockVersion = null)
 * @method SiteView|null findOneBy(array $criteria, array $orderBy = null)
 * @method SiteView[]    findAll()
 * @method SiteView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteView::class);
    }

    public function save(SiteView $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SiteView $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function updateCounter(): void
    {
        $query = 'UPDATE \App\Entity\SiteView c
                    SET c.count = c.count + 1
                    WHERE c.id = 1';

        $result = $this->getEntityManager()->createQuery($query);
        $result->execute();
    }

    public function getCount()
    {
        $result = $this->createQueryBuilder('c')
            ->select('c.count')
            ->where('c.id = 1')
            ->getQuery()
            ->getResult()
        ;

        return $result[0]['count'];
    }
}
