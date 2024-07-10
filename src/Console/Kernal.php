<?php

namespace PulseFrame\Console;

use PulseFrame\Facades\Config;
use PulseFrame\Console\Extra\Logo;
use PulseFrame\Foundation\Application as PulseApplication;
use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\ConsoleOutput;

class Kernal
{
  public static $application;

  public function __construct()
  {
    $application = new Application('');
    $this->loadCommands($application);
    self::$application = $application;
  }

  private function loadCommands(Application $application)
  {
    $output = new ConsoleOutput();
    new Logo($output);
    
    $finder = new Finder();
    $finder->files()->in(__DIR__ . '/Commands')->name('*Command.php');

    foreach ($finder as $file) {
      $className = $this->getClassNameFromFile($file->getRealPath());
      if (class_exists($className)) {
        $application->add(new $className());
      }
    }
  }

  private function getClassNameFromFile($filePath)
  {
    $namespace = 'PulseFrame\\Console\\Commands';
    $className = basename($filePath, '.php');
    return $namespace . '\\' . $className;
  }
}