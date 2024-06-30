<?php

namespace PulseFrame\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
  protected static $defaultName = 'database:migrate';

  protected function configure()
  {
    $this
      ->setName(self::$defaultName)
      ->setDescription('Run the database migrations');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $migrationFiles = glob(ROOT_DIR . '/database/migrations/*.php');

    foreach ($migrationFiles as $file) {
      require_once $file;
      $fileName = basename($file, '.php');
      $className = $this->getClassNameFromFileName($fileName);
      $fullClassName = "\\PulseFrame\\database\\migrations\\$className";

      if (class_exists($fullClassName)) {
        $migration = new $fullClassName();
        $migration->up();
        $output->writeln('<info>Migrated: ' . $fileName . '</info>');
      } else {
        $output->writeln('<error>Migration class not found: ' . $fullClassName . '</error>');
      }
    }

    return Command::SUCCESS;
  }

  private function getClassNameFromFileName($fileName)
  {
    $parts = explode('_', $fileName, 2);
    return basename($parts[1], '.php');
  }
}
