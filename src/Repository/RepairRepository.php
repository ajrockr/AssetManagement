<?php

namespace App\Repository;

use App\Entity\Repair;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        $repairs = $this->createQueryBuilder('r')
            ->select('r')
            ->getQuery()
            ->getScalarResult()
        ;

        $return = [];
        foreach ($repairs as $repair) {
            $return[$repair['r_id']] = [
                'id' => $repair['r_id'],
                'asset_id' => $repair['r_assetId'],
                'created_date' => $repair['r_createdDate'],
                'started_date' => $repair['r_startedDate'],
                'modified_date' => $repair['r_lastModifiedDate'],
                'resolved_date' => $repair['r_resolvedDate'],
                'technician' => $repair['r_technicianId'],
                'issue' => $repair['r_issue'],
                'parts_needed' => $repair['r_partsNeeded'],
                'actions_performed' => $repair['r_actionsPerformed'],
                'status' => $repair['r_status'],
                'users_following' => $repair['r_usersFollowing'],
                'asset_identifier' => $repair['r_assetUniqueIdentifier']
            ];
        }

        return $return;
    }
}
