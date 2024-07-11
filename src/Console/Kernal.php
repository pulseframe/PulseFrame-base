<?php

namespace PulseFrame\Console;

use PulseFrame\Facades\Config;
use PulseFrame\Console\Extra\Logo;
use PulseFrame\Foundation\Application as PulseApplication;
use App\Console\Kernal as AppConsoleKernal;
use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\ConsoleOutput;

class Kernal
{
  public static $application;

  public static function initialize() {
    $instance = new self();
    return $instance->initializeInstance();
  }

  public function initializeInstance()
  {
    $application = new Application('');

    self::$application = $application;
    
    $this->loadCommands();

    new AppConsoleKernal(self::$application);

    return $application->run();
  }

  private function loadCommands()
  {
    $output = new ConsoleOutput();
    new Logo($output);
    
    $finder = new Finder();
    $finder->files()->in(__DIR__ . '/Commands')->name('*Command.php');

    foreach ($finder as $file) {
      $className = $this->getClassNameFromFile($file->getRealPath());
      if (class_exists($className)) {
        self::$application->add(new $className());
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