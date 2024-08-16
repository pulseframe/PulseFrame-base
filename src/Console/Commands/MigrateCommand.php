<?php

namespace PulseFrame\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use PulseFrame\Database\Models\PulseFrameModel;
use PulseFrame\Database\Seeder;
use PulseFrame\Database\Migration;
use PulseFrame\Facades\Database;
use PulseFrame\Methods\Normal\UUID;

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
    $migrationDirectories = [
      ROOT_DIR . '/database/migrations',
      __DIR__ . '/../../../../database/src/Database/Migrations'
    ];

    $seederDirectories = [
      ROOT_DIR . '/database/seeders',
      __DIR__ . '/../../../../database/src/Database/Seeders'
    ];

    $this->migrate($migrationDirectories, $output);

    $this->seed($seederDirectories, $output);

    return Command::SUCCESS;
  }

  private function migrate(array $directories, OutputInterface $output): void
  {
    $finder = new Finder();
    $finder->files()->in($directories)->name('*.php');

    $pulseFrameMigrations = [];
    $appMigrations = [];

    foreach ($finder as $file) {
      require $file->getRealPath();
      $fileName = $file->getFilenameWithoutExtension();
      $className = $this->getClassNameFromFileName($fileName);
      if (strpos($file->getPathname(), ROOT_DIR . '/database/migrations') !== false) {
        $appMigrations[] = ['class' => $className, 'name' => $fileName];
      } elseif (strpos($file->getPathname(), __DIR__ . '/../../Database/Migrations') !== false) {
        $pulseFrameMigrations[] = ['class' => $className, 'name' => $fileName];
      }
    }

    foreach ($pulseFrameMigrations as $className) {
      $fullClassName = "\\PulseFrame\\Database\\Migrations\\" . $className['class'];
      if (class_exists($fullClassName)) {
        $migration = new $fullClassName();
        if ($migration instanceof Migration) {
          $migration->up();
          $existingData = Database::All(PulseFrameModel::class, ['data' => json_encode([$className['name'] => true])]);
          if (!$existingData) {
            $data = [
              'id' => UUID::Generate(),
              'data' => json_encode([
                $className['name'] => true
              ]),
              'timestamp' => date('Y-m-d H:i:s')
            ];
            Database::insert(PulseFrameModel::class, $data);
          }
          $output->writeln('<info>Migrated (PulseFrame): ' . $className['name'] . '</info>');
        } else {
          $output->writeln('<error>Class does not extend migration (PulseFrame): ' . $className['name'] . '</error>');
        }
      } else {
        $output->writeln('<error>migration class not found (PulseFrame): ' . $className['name'] . '</error>');
      }
    }

    foreach ($appMigrations as $className) {
      $fullClassName = "\\App\\Database\\migrations\\" . $className['class'];
      if (class_exists($fullClassName)) {
        $migration = new $fullClassName();
        if ($migration instanceof Migration) {
          $migration->up();
          $output->writeln('<info>Migrated: ' . $className['name'] . '</info>');
        } else {
          $output->writeln('<error>Class does not extend Migration: ' . $className['name'] . '</error>');
        }
      } else {
        $output->writeln('<error>Migration class not found: ' . $className['name'] . '</error>');
      }
    }
  }

  private function seed(array $directories, OutputInterface $output): void
  {
    $finder = new Finder();
    $finder->files()->in($directories)->name('*.php');

    $pulseFrameSeeders = [];
    $appSeeders = [];

    foreach ($finder as $file) {
      require $file->getRealPath();
      $fileName = $file->getFilenameWithoutExtension();
      $className = $this->getClassNameFromFileName($fileName);
      if (strpos($file->getPathname(), ROOT_DIR . '/database/seeders') !== false) {
        $appSeeders[] = $className;
      } elseif (strpos($file->getPathname(), __DIR__ . '/../../Database/Seeders') !== false) {
        $pulseFrameSeeders[] = $className;
      }
    }

    foreach ($pulseFrameSeeders as $className) {
      $fullClassName = "\\PulseFrame\\Database\\Seeders\\$className";
      if (class_exists($fullClassName)) {
        $seeder = new $fullClassName();
        if ($seeder instanceof Seeder) {
          $seeder->run($output);
          $output->writeln('<info>Seeded (PulseFrame): ' . $fileName . '</info>');
        } else {
          $output->writeln('<error>Class does not extend Seeder (PulseFrame): ' . $fullClassName . '</error>');
        }
      } else {
        $output->writeln('<error>Seeder class not found (PulseFrame): ' . $fullClassName . '</error>');
      }
    }

    foreach ($appSeeders as $className) {
      $fullClassName = "\\App\\Database\\seeders\\$className";
      if (class_exists($fullClassName)) {
        $seeder = new $fullClassName();
        if ($seeder instanceof Seeder) {
          $seeder->run($output);
          $output->writeln('<info>Seeded: ' . $fileName . '</info>');
        } else {
          $output->writeln('<error>Class does not extend Seeder: ' . $fullClassName . '</error>');
        }
      } else {
        $output->writeln('<error>Seeder class not found: ' . $fullClassName . '</error>');
      }
    }
  }

  private function getClassNameFromFileName($fileName)
  {
    if (strpos($fileName, '_') !== false) {
      return substr($fileName, strpos($fileName, '_') + 1);
    }
    return $fileName;
  }
}
