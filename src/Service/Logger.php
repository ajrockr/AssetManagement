<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;

class Logger
{
    // TODO I don't know what I'm doing with these constants... Figure it out later
    public const SOURCE_IMPORT_USER = 'admin_import_user';
    public const SOURCE_USER_LOGIN = 'user_login';
    public const ACTION_ADMIN = 'admin_action';
    public const ACTION_IMPORT_USER = 'import_user';
    public const ACTION_USER_LOGIN = 'user_login';

    public const ACTION_ASSET_CHECKIN = 'asset_checkin';

    private const TYPE_ADMIN = 'ADMIN';

    private const TYPE_SECURITY = 'SECURITY';

    private const TYPE_ASSET = 'ASSET';


    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * @param int $userId
     * @param array $importedUsers
     * @param string $source
     * @return void
     */
    public function importUsers(int $userId, array $importedUsers, string $source): void
    {
        $log = new Log();
        $log->setUserId($userId)
            ->setSourcepage($source)
            ->setAction(self::ACTION_IMPORT_USER)
            ->setType(self::TYPE_ADMIN)
            ->setDatetime(new \DateTimeImmutable('now'))
            ->setMessage([
                'message' => 'Imported Users',
                'users' => $importedUsers
            ])
        ;
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * @param int $userId
     * @param string $ipAddress
     * @param string $username
     * @param bool $successful
     * @return void
     */
    public function userLogin(int $userId, string $ipAddress, string $username, bool $successful): void
    {
        $log = new Log();
        $log->setUserid($userId)
            ->setSourcepage(null)
            ->setDatetime(new \DateTimeImmutable('now'))
            ->setType(self::TYPE_SECURITY)
            ->setAction(self::ACTION_USER_LOGIN)
            ->setMessage([
                'message' => 'User logged in',
                'ip_address' => $ipAddress,
                'username' => $username,
                'success' => $successful
            ])
        ;
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * @param int $userId
     * @param string $source
     * @param string $action
     * @param string|null $target
     * @return void
     */
    public function adminAction(int $userId, string $source, string $action, ?string $target = null): void
    {
        $log = new Log();
        $log->setType(self::TYPE_ADMIN)
            ->setAction($action)
            ->setSourcepage($source)
            ->setUserid($userId)
            ->setMessage([
                'message' => 'Admin Action',
                'target' => $target,
            ])
            ->setDatetime(new \DateTimeImmutable('now'))
        ;
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * @param int $userId
     * @param int $assetId
     * @param string $action
     * @param string $sourcePage
     * @param string|null $person
     * @param int|null $slotId
     * @return void
     */
    public function assetCheckInOut(int $userId, int $assetId, string $action, string $sourcePage, ?string $person = null, ?int $slotId = null): void
    {
        $log = new Log();
        $log->setType(self::TYPE_ASSET)
            ->setAction($action)
            ->setSourcepage($sourcePage)
            ->setUserid($userId)
            ->setMessage([
                'message' => 'Asset check in/out',
                'person' => $person
            ])
        ;
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
