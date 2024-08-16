<?php

namespace PulseFrame\Console\Commands;

use PulseFrame\Facades\Env;
use PulseFrame\Facades\Storage;
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
      Storage::delete('/framework/maintenance.flag');
      $output->writeln('<info>Maintenance mode disabled.</info>');
    } else {
      $uuid = $this->uuidv4();
      Storage::put('/framework/maintenance.flag', $uuid);
      $output->writeln('<info>To activate the maintenance bypass: ' . Env::get("app.url") . "/activate/" . $uuid . '</info>');
      $output->writeln('<info>Maintenance mode enabled</info>');
    }

    return Command::SUCCESS;
  }
  protected function uuidv4()
  {
    $data = random_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }
}
