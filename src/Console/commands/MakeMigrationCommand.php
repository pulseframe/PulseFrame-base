<?php

namespace PulseFrame\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMigrationCommand extends Command
{
  protected static $defaultName = 'make:migration';

  protected function configure()
  {
    $this
      ->setName(self::$defaultName)
      ->setDescription('Creates a new migration file')
      ->addArgument('name', InputArgument::REQUIRED, 'The name of the migration');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $name = $input->getArgument('name');
    $timestamp = date('Y-m-d-H:i:s');
    $filename = $timestamp . '_' . $name . '.php';
    $filepath = ROOT_DIR . '/database/migrations/' . $filename;

    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    $template = $this->getMigrationTemplate($className);
    file_put_contents($filepath, $template);

    $output->writeln('<info>Migration created: ' . $filename . '</info>');
    return Command::SUCCESS;
  }

  private function getMigrationTemplate($className)
  {
    return <<<PHP
    <?php

    namespace App\Database\Migrations;
    
    use PulseFrame\Database\Migration;
    
    class {$className} extends Migration
    {
      protected \$table = 'your_table_name';
    
      public function up()
      {
        \$this->createTable([
          'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
          'name' => 'VARCHAR(255) NOT NULL',
          'email' => 'VARCHAR(255) NOT NULL',
          'password' => 'VARCHAR(255) NOT NULL',
          'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        ]);
      }
    
      public function down()
      {
        \$this->dropTable();
      }
    }
    PHP;
  }
}
