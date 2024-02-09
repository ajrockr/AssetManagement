<?php

namespace App\Repository;

use App\Entity\AlertMessage;
use App\Entity\SiteConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AlertMessage>
 *
 * @method AlertMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlertMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlertMessage[]    findAll()
 * @method AlertMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlertMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlertMessage::class);
    }

    public function save(AlertMessage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AlertMessage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getActiveMessages(): ?array
    {
        return $this->createQueryBuilder('am')
            ->select('am.subject', 'am.message')
            ->orderBy('am.dateCreated', 'DESC')
            ->andWhere('am.active = 1')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getAllMessages(string $sort = 'DESC', string $source = 'all'): ?AlertMessage
    {
        $qb = $this->createQueryBuilder('am')
            ->select('am.subject', 'am.message', 'am.dateCreated', 'am.source', 'am.active')
            ->orderBy('am.dateCreated', ':sort')->setParameter('sort', $sort);

        if ($source === 'all') {
            $qb->andWhere('am.source = :source')->setParameter('source', $source);
        }

        return $qb->getQuery()->getResult();
    }

    public function isActiveMessage(): ?AlertMessage
    {
        return $this->createQueryBuilder('am')
            ->select('am.active')
            ->where('am.active = 1')
            ->orWhere('am.active = true')
            ->getQuery()
            ->getResult()
        ;
    }

    public function alertsEnabled(): bool
    {
        return $this->getEntityManager()->getRepository(SiteConfig::class)->findOneByName('site_alertMessageEnabled') == "1";
    }
}
