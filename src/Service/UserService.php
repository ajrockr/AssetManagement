<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserService
{
    public function __construct(
        private readonly RoleHierarchyInterface $roleHierarchy,
    ) {}

    public function hasRole(array|string $targetRoles, array $userRoles): bool
    {
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames(['ROLE_REPAIR_TECHNICIAN']);
        $reachableRoles[] = 'ROLE_SUPER_ADMIN';

        return !empty(array_intersect($reachableRoles, $userRoles));
    }
}
