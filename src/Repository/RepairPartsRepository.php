<?php

namespace App\Repository;

use App\Entity\RepairParts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RepairParts>
 *
 * @method RepairParts|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepairParts|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepairParts[]    findAll()
 * @method RepairParts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepairPartsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepairParts::class);
    }

    public function save(RepairParts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RepairParts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllParts(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->getQuery()
            ->getArrayResult();
    }
}
