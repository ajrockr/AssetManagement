<?php

namespace App\Controller;

use App\Repository\LogRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoggerController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LogRepository $logRepository,
    ) { }

    /**
     * @param int $userId
     * @param array $importedUsers
     * @param string $source
     * @return void
     */
    public function importUsers(int $userId, array $importedUsers, string $source): void
    {
        $this->logRepository->userImport($userId, $importedUsers, $source, 'info', 'user_import');
    }

    /**
     * @param string $username
     * @param string $ipAddress
     * @return void
     */
    public function userLogin(string $username, string $ipAddress): void
    {
        $now = new \DateTimeImmutable('now');
        $this->logger->info('LOGIN', [
            'username' => $username,
            'ipaddress' => $ipAddress,
            'datetime' => $now,
        ]);
    }

    /**
     * @param string $username
     * @param string $source
     * @param string $action
     * @param string $target
     * @return void
     */
    public function adminAction(string $username, string $source, string $action, string $target = 'null'): void
    {
        $now = new \DateTimeImmutable('now');
        $this->logger->info('ADMIN: Action performed.', [
            'action' => $action,
            'username' => $username,
            'target' => $target,
            'source' => $source,
            'datetime' => $now,
        ]);
    }
}
