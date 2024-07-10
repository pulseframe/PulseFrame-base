<?php

namespace PulseFrame\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RollbackCommand extends Command
{
  protected static $defaultName = 'database:rollback';

  protected function configure()
  {
    $this
      ->setName(self::$defaultName)
      ->setDescription('Rollback the last database migration');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $migrationFiles = glob(ROOT_DIR . '/database/migrations/*.php');
    rsort($migrationFiles);

    foreach ($migrationFiles as $file) {
      require_once $file;
      $fileName = basename($file, '.php');
      $className = $this->getClassNameFromFileName($fileName);
      $fullClassName = "App\\database\\migrations\\$className";

      if (class_exists($fullClassName)) {
        $migration = new $fullClassName;
        $migration->down();
        $output->writeln('<info>Rolled back: ' . $className . '</info>');
        break;
      } else {
        $output->writeln('<error>Migration class not found: ' . $className . '</error>');
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
