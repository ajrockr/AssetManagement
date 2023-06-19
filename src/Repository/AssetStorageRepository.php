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

    public function storageDataExists($value): bool|array
    {
        $query = $this->findAll();
        $array_walk = [];
        foreach ($query as $storage) {
            $array_walk[$storage->getId()] = $storage->getStorageData();
        }

//        return $this->arraySearchRecursive($value, $array_walk);
        foreach ($array_walk as $storage => $storageData) {
            if ($this->arraySearchRecursive($value, $storageData)) {
                return [ 'id' => $storage];
            }
        }

        return false;
    }

    public function getStorageData(int $id): array
    {
        $qb = $this->createQueryBuilder('s');
        $result = $qb->select('s.storageData')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getArrayResult();
        $slots = [];
        foreach ($result as $data) {
            foreach ($data as $side) {
                foreach ($side as $row) {
                    foreach ($row as $i => $slot) {
                        $slots[] = $slot;
                    }
                }
            }
        }

        $flattened_array = [];
        array_walk_recursive($slots, function($a) use (&$flattened_array) {
           $flattened_array[] = $a;
        });

        return $flattened_array;
    }

    private function arraySearchRecursive(mixed $term, array $array): bool
    {
//        foreach ($haystack as $array) {
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
//        }

        return false;
    }

    public function getAll()
    {
        return $this->createQueryBuilder('as')
            ->select('as')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getOne(int $id)
    {
        return $this->createQueryBuilder('as')
            ->select('as')
            ->getQuery()
            ->getResult()
        ;
    }
}
