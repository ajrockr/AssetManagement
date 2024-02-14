<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserService
{
    public function __construct(
        private readonly RoleHierarchyInterface $roleHierarchy,
    ) {}

    public function hasRole(array $targetRoles, array $userRoles): bool
    {
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($targetRoles);
        array_pop($reachableRoles);
        $reachableRoles[] = 'ROLE_SUPER_ADMIN';

        return !empty(array_intersect($reachableRoles, $userRoles));
    }
}
