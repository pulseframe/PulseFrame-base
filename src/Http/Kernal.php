<?php
namespace PulseFrame\Http;

use PulseFrame\Facades\Config;

class Kernal
{
  private static $services = [];

  private static function set($key, $service)
  {
    self::$services[$key] = $service;
  }

  private static function registerServices()
  {
    $services = Config::get('app', 'register');
    if (is_array($services)) {
      foreach ($services as $service) {
        self::set($service, $service);
      }
    } else {
      throw new \Exception("Invalid configuration: 'register' array not found or not an array.");
    }
  }

  public static function initialize()
  {
    self::registerServices();
    foreach (self::$services as $service) {
      if (is_callable([$service, 'initialize'])) {
        $service::initialize();
      }
    }
  }
}