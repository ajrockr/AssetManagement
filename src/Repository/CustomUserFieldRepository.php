<?php

namespace App\Repository;

use App\Entity\CustomUserField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomUserField>
 *
 * @method CustomUserField|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomUserField|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomUserField[]    findAll()
 * @method CustomUserField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomUserFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomUserField::class);
    }

    public function save(CustomUserField $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CustomUserField $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
