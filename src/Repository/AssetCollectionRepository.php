<?php

namespace App\Repository;

use App\Entity\AssetCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function getCollectedAssetSlots(): array
    {
        $results =  $this->createQueryBuilder('ac')
            ->select('ac.collectionLocation')
            ->getQuery()
            ->getArrayResult()
        ;

        foreach ($results as $result) {
            $return[] = $result['collectionLocation'];
        }

        return $return;
    }

    public function getAll(): array
    {
        $assets = $this->createQueryBuilder('ac')
            ->select('ac')
            ->addSelect('a.assettag', 'a.serialnumber')
            ->innerJoin('App\Entity\Asset', 'a', 'WHERE', 'a.id = ac.DeviceID')
            ->getQuery()
            ->getArrayResult()
        ;

        $return = [];
        foreach ($assets as $asset) {
            $return[] = [
                'id' => $asset[0]['id'],
                'asset_id' => $asset[0]['DeviceID'],
                'collected_from' => $asset[0]['CollectedFrom'],
                'collected_by' => $asset[0]['CollectedBy'],
                'collected_date' => $asset[0]['collectedDate'],
                'notes' => $asset[0]['collectionNotes'],
                'location' => $asset[0]['collectionLocation'],
                'checked_out' => $asset[0]['checkedout'],
                'processed' => $asset[0]['processed'],
                'asset_tag' => $asset['assettag'],
                'serial_number' => $asset['serialnumber']
            ];
        }

        return $return;
    }

    public function removeCollection(int|array $locations)
    {
        $conditions = preg_filter('/^/', 'ac.collectionLocation = ', $locations);
        $qb = $this->createQueryBuilder('ac');
        $delete = $qb->delete()
            ->where(
                $qb->expr()->orX()->addMultiple($conditions)
            )
            ->getQuery()
            ->execute();
    }
}
