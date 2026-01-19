<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

namespace App\AdminUser\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\AdminUser\Entity\AdminUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof AdminUser) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        // Hash password if it's set (for create and update operations)
        if ($data->getAdminPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $data,
                $data->getAdminPassword()
            );
            $data->setAdminPassword($hashedPassword);
        }

        // Call the decorated processor to persist the entity
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}

