<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\AdminUser\Command;

use App\AdminUser\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// php bin/console app:reset-admin-password admin_name new_password
#[AsCommand(
    name: 'app:reset-admin-password',
    description: 'Reset password for an existing admin user.',
)]
class ResetAdminPasswordCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUserRepository $adminUserRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('admin_name', InputArgument::REQUIRED, 'Admin name')
            ->addArgument('new_password', InputArgument::REQUIRED, 'New password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adminName = $input->getArgument('admin_name');
        $newPassword = $input->getArgument('new_password');

        $adminUser = $this->adminUserRepository->findOneBy(['admin_name' => $adminName]);

        if (!$adminUser) {
            $output->writeln('<error>Admin user "' . $adminName . '" not found.</error>');
            return Command::FAILURE;
        }

        $adminUser->setAdminPassword($newPassword);

        $this->entityManager->flush();

        $output->writeln('<info>Password for "' . $adminName . '" has been reset successfully.</info>');
        return Command::SUCCESS;
    }
}
