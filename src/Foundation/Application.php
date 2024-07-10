<?php

namespace PulseFrame\Foundation;

use PulseFrame\Facades\Env;

class Application
{
  public static $VERSION = "1.1.0";
  public static $STAGE = "BETA";

  private static $instance;
  private $singletons = [];
  private $facadeInstances = [];

  public static $initialized;

  public function __construct()
  {
    if (self::$instance) {
      throw new \RuntimeException('An instance of the Application class already exists.');
    }

    self::$instance = $this;
    define('ROOT_DIR', __DIR__ . "/../../../../../");

    if (session_status() === PHP_SESSION_NONE) {
      session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'use_strict_mode' => true
      ]);
    }

    $this->loadSingletons();
  }

  private function loadSingletons() {
    $this->singleton([Env::class, 'load']);
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
    if (self::$instance !== null) {
      self::getInstance();
      self::$initialized = true;
    }

    $callableKey = $this->getCallableKey($callable);

    if (isset($this->singletons[$callableKey])) {
      throw new \RuntimeException("The callable $callableKey is already initialized.");
    }

    $this->singletons[$callableKey] = call_user_func($callable);
    return $this->singletons[$callableKey];
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
