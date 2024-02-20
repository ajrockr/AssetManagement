<?php

namespace App\Repository;

use App\Entity\Repair;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Repair>
 *
 * @method Repair|null find($id, $lockMode = null, $lockVersion = null)
 * @method Repair|null findOneBy(array $criteria, array $orderBy = null)
 * @method Repair[]    findAll()
 * @method Repair[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepairRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repair::class);
    }

    public function save(Repair $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Repair $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAll(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r, user.firstname, user.surname')
            ->leftJoin('App\Entity\User', 'user', 'WITH', 'user.id = r.technicianId')
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getRepair(int $assetId, bool $onlyOpen = false): ?Repair
    {
        $query = $this->createQueryBuilder('r')
            ->select('r')
            ->andWhere('r.assetId = :assetId');

        if ($onlyOpen) {
            $query->andWhere('r.status != :closed')
                ->setParameter('closed', Repair::STATUS_CLOSED);
        }

        return $query->setParameter('assetId', $assetId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAllOpen(): array
    {
        $qb = $this->createQueryBuilder('r');
        return $qb->select('r, user.firstname, user.surname')
            ->where($qb->expr()->neq('r.status', ':repairstatus'))
            ->leftJoin('App\Entity\User', 'user', 'WITH', 'user.id = r.technicianId')
            ->setParameter('repairstatus', 'status_resolved')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR)
        ;
    }

    /**
     * getCount
     *
     * @return int
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getCount(): int
    {
        return $this->createQueryBuilder('repair')
            ->select('count(repair.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return array
     */
    public function getCountAndCreatedDate(): array
    {
        return $this->createQueryBuilder('repair')
            ->select('repair.createdDate')
            ->getQuery()
            ->getResult()
        ;
    }
}
