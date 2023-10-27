<?php

namespace App\Repository;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    /**
     * getUserCount
     *
     * @param  mixed $department
     * @return int
     */
    public function getUserCount(?string $department = null): int
    {
        if ($department) {
            return $this->createQueryBuilder('user')
                ->select('COUNT(user.id)')
                ->where('user.department = :department')
                ->setParameter('department', $department)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        return $this->createQueryBuilder('user')
            ->select('COUNT(user.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * getLastCreatedUser
     *
     * @return array
     */
    public function getLastCreatedUser(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.surname', 'u.firstname')
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }

    public function adminImportUsers(array $users)
    {

    }

    /**
     * getUsers
     *
     * @return array
     */
    public function getUsers(): array
    {
        $users = $this->createQueryBuilder('u')
            ->select('u')
            ->getQuery()
            ->getScalarResult()
        ;

        $return = [];
        foreach ($users as $user) {
            $return[$user['u_id']] = [
                'id' => $user['u_id'],
                'username' => $user['u_username'],
                'roles' => $user['u_roles'],
                'email' => $user['u_email'],
                'location' => $user['u_location'],
                'department' => $user['u_department'],
                'phone' => $user['u_phone'],
                'phone_extension' => $user['u_extension'],
                'title' => $user['u_title'],
                'homepage' => $user['u_homepage'],
                'manager' => $user['u_manager'],
                'date_created' => $user['u_dateCreated'],
                'surname' => $user['u_surname'],
                'firstname' => $user['u_firstname'],
                'enabled' => (bool) $user['u_enabled'],
                'pending' => (bool) $user['u_pending'],
                'avatar' => $user['u_avatar'],
                'unique_id' => $user['u_userUniqueId'],
                'type' => $user['u_type']
            ];
        }

        return $return;
    }

    /**
     * setPendingStatus
     *
     * @param  int $id
     * @param  bool $pending
     * @return void
     */
    public function setPendingStatus(int $id, bool $pending = true): void
    {
        $this->createQueryBuilder('u')
            ->update()
            ->set('u.pending', $pending)
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * setLastActiveTime
     *
     * @param  int $id
     * @param  DateTimeImmutable $time
     * @return void
     */
    public function setLastActiveTime(int $id, DateTimeImmutable $time): void
    {
        $this->createQueryBuilder('user')
            ->update()
            ->set('user.lastActivity', ':datetime')
            ->where('user.id = :userId')
            ->setParameter('datetime', $time)
            ->setParameter('userId', $id)
            ->getQuery()
            ->execute()
        ;
    }

    public function getLastActiveTime(?int $id = null)
    {
        $query = $this->createQueryBuilder('user')
            ->select('user.lastActivity, user.id, user.username, user.surname, user.firstname');

        if ($id) {
            $query->where('user.id = :userId')
                ->setParameter('userId', $id);
        }

        $query->andWhere('user.lastActivity IS NOT NULL');

        return $query->getQuery()
        ->getArrayResult();
    }
}
