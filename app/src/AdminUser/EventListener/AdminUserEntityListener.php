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

class AdminUserEntityListener
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Hash password before persisting new AdminUser
     */
    public function prePersist(AdminUser $adminUser, PrePersistEventArgs $args): void
    {
        $this->hashPassword($adminUser);
    }

    /**
     * Hash password before updating AdminUser
     */
    public function preUpdate(AdminUser $adminUser, PreUpdateEventArgs $args): void
    {
        $this->hashPassword($adminUser);

        // If password was changed, mark it for update
        if ($args->hasChangedField('admin_password')) {
            $entityManager = $args->getObjectManager();
            $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet(
                $entityManager->getClassMetadata(AdminUser::class),
                $adminUser
            );
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
                $adminUser->getAdminPassword()
            );
            $adminUser->setAdminPassword($hashedPassword);

            $this->logger->info('Password hashed for admin user', [
                'admin_name' => $adminUser->getAdminName()
            ]);
        }
    }
}

