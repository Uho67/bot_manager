<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

namespace App\AdminUser\Command;

use App\AdminUser\Entity\AdminUser;
use App\AdminUser\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Creates a new admin user.',
)]
class CreateAdminUserCommand extends Command
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
            ->addArgument('admin_password', InputArgument::REQUIRED, 'Admin password')
            ->addArgument('bot_code', InputArgument::OPTIONAL, 'Bot code')
            ->addOption('role', null, InputOption::VALUE_OPTIONAL, 'Admin role (ADMIN or SUPER_ADMIN)', 'ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adminName = $input->getArgument('admin_name');
        $roleInput = strtoupper($input->getOption('role'));
        $validRoles = ['ADMIN', 'SUPER_ADMIN'];
        if (!in_array($roleInput, $validRoles, true)) {
            $output->writeln('<error>Invalid role. Allowed values: ADMIN, SUPER_ADMIN.</error>');
            return Command::FAILURE;
        }
        $role = $roleInput === 'SUPER_ADMIN' ? 'ROLE_SUPER_ADMIN' : 'ROLE_ADMIN';

        if ($this->adminUserRepository->findOneBy(['admin_name' => $adminName])) {
            $output->writeln('<error>Admin user already exists.</error>');
            return Command::FAILURE;
        }

        $adminUser = new AdminUser();
        $adminUser->setAdminName($adminName);
        $adminUser->setAdminPassword(
            $this->passwordHasher->hashPassword($adminUser, $input->getArgument('admin_password'))
        );
        $adminUser->setBotCode($input->getArgument('bot_code'));
        $adminUser->setRoles([$role]);

        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        $output->writeln('<info>Admin user created successfully with role: ' . $role . '.</info>');
        return Command::SUCCESS;
    }
}
