<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Config\Command;

use App\Config\Service\ConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:config:set',
    description: 'Set configuration value for a bot',
)]
class ConfigSetCommand extends Command
{
    public function __construct(
        private readonly ConfigService $configService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('bot_identifier', InputArgument::REQUIRED, 'Bot identifier')
            ->addArgument('path', InputArgument::REQUIRED, 'Configuration path (e.g., admin/name)')
            ->addArgument('value', InputArgument::REQUIRED, 'Configuration value')
            ->addArgument('name', InputArgument::REQUIRED, 'Configuration display name')
            ->setHelp(<<<'HELP'
Set configuration value for a bot:
  <info>php bin/console app:config:set bot1 admin/name "Admin Name" "Admin Name Field"</info>
  <info>php bin/console app:config:set bot1 admin/email "admin@example.com" "Admin Email"</info>
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $botIdentifier = $input->getArgument('bot_identifier');
        $path = $input->getArgument('path');
        $value = $input->getArgument('value');
        $name = $input->getArgument('name');

        try {
            $config = $this->configService->set($botIdentifier, $path, $value, $name);

            $io->success(sprintf(
                'Configuration saved: [%s] %s = %s (%s)',
                $botIdentifier,
                $path,
                $value,
                $name
            ));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to save configuration: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

