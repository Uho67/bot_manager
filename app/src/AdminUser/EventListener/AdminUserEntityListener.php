<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\AdminUser\EventListener;

use App\AdminUser\Entity\AdminUser;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class AdminUserEntityListener
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Hash password and ensure ADMIN role before persisting new AdminUser
     */
    public function prePersist(AdminUser $adminUser, PrePersistEventArgs $args): void
    {
        $this->ensureAdminRole($adminUser);
        $this->hashPassword($adminUser);
    }

    /**
     * Hash password and ensure ADMIN role before updating AdminUser
     */
    public function preUpdate(AdminUser $adminUser, PreUpdateEventArgs $args): void
    {
        $this->ensureAdminRole($adminUser);
        $this->hashPassword($adminUser);

        // If password was changed, mark it for update
        if ($args->hasChangedField('admin_password')) {
            $entityManager = $args->getObjectManager();
            $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet(
                $entityManager->getClassMetadata(AdminUser::class),
                $adminUser,
            );
        }
    }

    /**
     * Ensure user has at least ADMIN role if not already SUPER_ADMIN or ADMIN
     */
    private function ensureAdminRole(AdminUser $adminUser): void
    {
        $roles = $adminUser->getRoles();
        if (!\in_array('ROLE_ADMIN', $roles, true) && !\in_array('ROLE_SUPER_ADMIN', $roles, true)) {
            $roles = ['ROLE_ADMIN'];
            $adminUser->setRoles($roles);
        }
    }

    /**
     * Hash password if it's set
     */
    private function hashPassword(AdminUser $adminUser): void
    {
        if ($adminUser->getAdminPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $adminUser,
                $adminUser->getAdminPassword(),
            );
            $adminUser->setAdminPassword($hashedPassword);

            $this->logger->info('Password hashed for admin user', [
                'admin_name' => $adminUser->getAdminName(),
            ]);
        }
    }
}
