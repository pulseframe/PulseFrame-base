<?php

namespace PulseFrame\Console\Commands;

use PulseFrame\Facades\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class RunCronJobsCommand extends Command
{
  protected static $defaultName = 'run:cronjobs';

  protected function configure()
  {
    $this
      ->setName(self::$defaultName)
      ->setDescription('Run scheduled cron jobs')
      ->addOption(
        'log-dir',
        null,
        InputOption::VALUE_OPTIONAL,
        'Directory to save logs',
        $_ENV['storage_path'] . '/logs/cronjobs/'
      )
      ->addOption(
        'job',
        null,
        InputOption::VALUE_OPTIONAL,
        'Run a specific job'
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $logDir = $input->getOption('log-dir');
    if (!file_exists($logDir) && !mkdir($logDir, 0755, true) && !is_dir($logDir)) {
      $output->writeln('<error>Failed to create log directory</error>');
      return Command::FAILURE;
    }

    $specificJob = $input->getOption('job');

    $jobFiles = glob(ROOT_DIR . '/app/jobs/*.php');
    foreach ($jobFiles as $jobFile) {
      $className = $this->getClassNameFromFile($jobFile);
      if ($specificJob && basename($jobFile, '.php') !== $specificJob) {
        continue;
      }
      $logFile = $this->generateLogFileName($logDir, basename($jobFile, '.php'));
      if ($className && class_exists($className) && method_exists($className, 'process')) {
        $this->runFunction($jobFile, $className, $logFile, $output);
      } else {
        file_put_contents($logFile, "Class $className not found or missing process method in $jobFile\n", FILE_APPEND);
      }
    }

    $output->writeln('Cron jobs ran.');
    return Command::SUCCESS;
  }

  private function getClassNameFromFile($filePath)
  {
    require_once $filePath;
    $className = basename($filePath, '.php');
    return 'App\\jobs\\' . $className;
  }

  private function generateLogFileName($logDir, $scriptName)
  {
    $logFileDir = $logDir . $scriptName;
    if (!file_exists($logFileDir) && !mkdir($logFileDir, 0755, true) && !is_dir($logFileDir)) {
      throw new \RuntimeException(sprintf('Directory "%s" was not created', $logFileDir));
    }
    return $logFileDir . '/cronjob-' . date('Y-m-d') . '.txt';
  }

  private function runFunction($classFile, $className, $logFile, OutputInterface $output)
  {
    if (!file_exists($classFile)) {
      $output->writeln('<error>File ' . $classFile . ' does not exist</error>');
      file_put_contents($logFile, "File $classFile does not exist\n", FILE_APPEND);
      return;
    }

    file_put_contents($logFile, '');

    require_once $classFile;
    $instance = new $className();

    set_error_handler(function ($severity, $message, $file, $line) use ($logFile) {
      $logMessage = "[Error][$severity] $message in $file on line $line\n";
      file_put_contents($logFile, $logMessage, FILE_APPEND);
      return true;
    });

    ob_start();
    try {
      if (method_exists($instance, 'setTimestampFunctions')) {
        $instance->setTimestampFunctions([$this, 'getLastRunTimestamp'], [$this, 'updateLastRunTimestamp']);
      }
      $output->writeln("<info>Class '$className' executed.</info>");
    } catch (\Exception $e) {
      file_put_contents($logFile, "[Exception] " . $e->getMessage() . "\n", FILE_APPEND);
    } finally {
      $logOutput = ob_get_clean();
      file_put_contents($logFile, $logOutput, FILE_APPEND);
      restore_error_handler();
    }
  }

  public function getLastRunTimestamp($jobName)
  {
    $result = Database::find('jobStatusModel', $jobName);

    if (!$result) {
      $this->createJobStatusEntry($jobName);
      return null;
    }

    return $result ? $result['last_run'] : null;
  }

  private function createJobStatusEntry($jobName)
  {
    $initialTimestamp = '1970-01-01 00:00:00';
    Database::insert('jobStatusModel', ['job_name' => $jobName, 'last_run' => $initialTimestamp]);
  }

  public function updateLastRunTimestamp($jobName)
  {
    $timestamp = date('Y-m-d H:i:s', time());
    Database::update('jobStatusModel', $jobName, ['last_run' => $timestamp]);
  }
}
