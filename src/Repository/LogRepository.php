<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function userImport(
        $userId,
        array $usersImported,
        string $source = 'admin/import_user',
        string $type = 'info',
        string $action = 'user_import')
    {
        $datetime = new \DateTimeImmutable('now');

        $entityManager = $this->getEntityManager();
        $log = new Log();
        $log->setUserId($userId)
            ->setAction($action)
            ->setDatetime($datetime)
            ->setType($type)
            ->setSourcepage($source)
            ->setMessage($usersImported);
        $entityManager->persist($log);
        $entityManager->flush();
    }
}
