<?php

namespace PulseFrame\Foundation;

use PulseFrame\Facades\Env;
use PulseFrame\Facades\Database;
use PulseFrame\Facades\Session;
use PulseFrame\Database\Models\PulseFrameModel;

class Application
{
  public const VERSION = "1.2";
  public const STAGE = "BETA";

  public static $instance;
  private $singletons = [];

  public static $initialized;

  public function __construct()
  {
    if (self::$instance) {
      throw new \RuntimeException('An instance of the Application class already exists.');
    }

    self::$initialized = true;

    self::$instance = $this;
    define('ROOT_DIR', __DIR__ . "/../../../../../");

    Session::start();

    $this->loadSingletons();

    if (empty(Env::get('app.key'))) {
      throw new \Exception("It seems there is no app key... You may have forgotten to generate it.");
    }
    try {
      $existingData = Database::All(PulseFrameModel::class, ['data' => json_encode(["CreatePulseFrame" => true])]);
      if ($existingData) {
        $data = Database::All(PulseFrameModel::class, ['id' => '328ef6b3-68d0-4f47-9ffe-7529d5d392b3']);
        $data = json_decode($data[0]['data'], true);
        if ($data['key'] !== Env::get('app.key')) {
          $PulseFrame = Database::find(PulseFrameModel::class, Env::get('app.key'));
          if (empty($PulseFrame)) {
            throw new \Exception("Uh oh! There seems to be a key mismatch, you must have changed your key!");
          }
        }
      }
    } catch (\Exception $e) {
    }
  }

  private function loadSingletons()
  {
    $this->singleton([Env::class, 'initialize']);
  }

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function singleton(callable $callable)
  {
    $callableKey = $this->getCallableKey($callable);

    if (!isset($this->singletons[$callableKey])) {
      $this->singletons[$callableKey] = [];
    }

    $this->singletons[$callableKey][] = $callable;

    if (count($this->singletons[$callableKey]) > 1) {
      throw new \RuntimeException("The callable $callableKey is already initialized.");
    }

    return call_user_func($callable);
  }

  private function getCallableKey(callable $callable): string
  {
    if (is_string($callable)) {
      return $callable;
    } elseif (is_array($callable)) {
      return implode('|', $callable);
    } elseif ($callable instanceof \Closure) {
      return spl_object_hash($callable);
    } else {
      throw new \InvalidArgumentException('Invalid callable type provided.');
    }
  }
}
