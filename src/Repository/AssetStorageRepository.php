<?php

namespace App\Repository;

use App\Entity\AssetStorage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AssetStorage>
 *
 * @method AssetStorage|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssetStorage|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssetStorage[]    findAll()
 * @method AssetStorage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssetStorageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetStorage::class);
    }

    public function save(AssetStorage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AssetStorage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function storageDataExists($value): bool
    {
        $query = $this->findAll();
        $array_walk = [];
        foreach ($query as $storage) {
            $array_walk[] = $storage->getStorageData();
        }

        return $this->arraySearchRecursive($value, $array_walk);
    }
    
    private function arraySearchRecursive(mixed $term, array $haystack): bool
    {
        foreach ($haystack as $array) {
            foreach ($array as $side) {
                foreach ($side as $row) {
                    foreach ($row as $key=>$val) {
                        if ($term != $val) {
                            continue;
                        }

                        return true;
                    }
                }
            }
        }

        return false;
    }

//    /**
//     * @return AssetStorage[] Returns an array of AssetStorage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AssetStorage
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
