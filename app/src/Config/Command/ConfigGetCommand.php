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
    name: 'app:config:get',
    description: 'Get configuration value for a bot',
)]
class ConfigGetCommand extends Command
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
            ->addArgument('path', InputArgument::OPTIONAL, 'Configuration path (shows all if not provided)')
            ->setHelp(
                <<<'HELP'
                    Get configuration value for a bot:
                      <info>php bin/console app:config:get bot1 admin/name</info>
                      <info>php bin/console app:config:get bot1</info> (shows all configs)
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $botIdentifier = $input->getArgument('bot_identifier');
        $path = $input->getArgument('path');

        if (!$path) {
            // Show all configs for this bot
            $configs = $this->configService->getAllForBot($botIdentifier);

            if (empty($configs)) {
                $io->warning('No configurations found for bot: ' . $botIdentifier);

                return Command::SUCCESS;
            }

            $rows = [];
            foreach ($configs as $config) {
                $rows[] = [
                    $config->getPath(),
                    $config->getValue(),
                    $config->getName(),
                ];
            }

            $io->table(['Path', 'Value', 'Name'], $rows);

            return Command::SUCCESS;
        }

        $value = $this->configService->get($botIdentifier, $path);

        $io->success(\sprintf('[%s] %s = %s', $botIdentifier, $path, $value));

        return Command::SUCCESS;
    }
}
