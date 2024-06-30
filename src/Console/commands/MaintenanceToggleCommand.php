<?php

namespace PulseFrame\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceToggleCommand extends Command
{
  protected static $defaultName = 'toggle:maintenance';

  protected function configure()
  {
    $this
      ->setName(self::$defaultName)
      ->setDescription('Toggles maintenance mode');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $maintenanceFile = $_ENV['storage_path'] . '/framework/maintenance.flag';

    if (file_exists($maintenanceFile)) {
      unlink($maintenanceFile);
      $output->writeln('<info>Maintenance mode disabled.</info>');
    } else {
      touch($maintenanceFile);
      $output->writeln('<info>Maintenance mode enabled.</info>');
    }

    return Command::SUCCESS;
  }
}
