<?php

namespace PulseFrame\Facades;

use Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Env
 * 
 * @category facades
 * @name Env
 * 
 * This class is responsible for loading and retrieving configuration data from .env or config.yml files.
 * It provides static methods to check for the existence of these files, load their contents, 
 * and retrieve specific configuration values.
 */
class Env
{
  private static $config = [];
  public static $envLoaded = false;

  /**
   * Load the environment or configuration file.
   *
   * @category facades
   * 
   * This function first checks if the config.yml file exists, if so, it loads the configuration from it.
   * If config.yml does not exist but .env file does, it will load the .env file. It throws an exception
   * if neither file is found.
   * 
   * Example usage:
   * Env::load();
   */
  public static function load()
  {
    if (file_exists(ROOT_DIR . '/config.yml')) {
      self::loadConfig();
    }

    if (file_exists(ROOT_DIR . '/.env')) {
      self::loadEnv();
    }

    if (!file_exists(ROOT_DIR . '/config.yml') && !file_exists(ROOT_DIR . '/.env')) {
      throw new \InvalidArgumentException('Neither config.yml nor .env file exists');
    }
  }

  /**
   * Load the .env file contents.
   *
   * @category facades
   * 
   * This private function loads the .env file using the Dotenv package if it has not already been loaded.
   * 
   * Example usage:
   * Env::loadEnv();
   */
  private static function loadEnv()
  {
    if (!self::$envLoaded) {
      $dotenv = Dotenv::createImmutable(ROOT_DIR);
      $dotenv->load();
      self::$envLoaded = true;
    }
  }

  /**
   * Load the config.yml file contents.
   *
   * @category facades
   * 
   * This public function reads the config.yml file and parses its contents using the Symfony YAML component.
   * It assigns the parsed data to the static $config property.
   * 
   * Example usage:
   * Env::loadConfig();
   */
  public static function loadConfig()
  {
    $configPath = ROOT_DIR . '/config.yml';

    if (!file_exists($configPath)) {
      throw new \InvalidArgumentException(sprintf('%s does not exist', $configPath));
    }

    self::$config = Yaml::parseFile($configPath);
  }

  /**
   * Get a configuration value by key.
   *
   * @category facades
   * 
   * @param string $key The key of the configuration value, supports dot notation (e.g., 'database.host').
   * @param mixed $default A default value to return if the key does not exist.
   * @return mixed The configuration value or the default value if the key is not found.
   *
   * This public function retrieves a value from the loaded configuration array using a dot-notated key.
   * It traverses the array according to the dot-separated sections of the key.
   * 
   * Example usage:
   * $value = Env::get('database.host', 'localhost');
   */
  public static function get($key = null, $default = null)
  {
    if ($key === null) {
      return self::$config;
    }

    $keys = explode('.', $key);
    $value = self::$config;

    foreach ($keys as $k) {
      if (isset($value[$k])) {
        $value = $value[$k];
      } else {
        return $default;
      }
    }
    return $value;
  }

  /**
   * Handle static method calls.
   *
   * @category facades
   * 
   * @param string $name The name of the method being called.
   * @param array $arguments The arguments passed to the method.
   * @return mixed The result of the method call.
   *
   * This magic method allows calling the get method statically. If 'get' is called as a static method,
   * it forwards the arguments to the get method. If any other method name is called, it throws an exception.
   * 
   * Example usage:
   * $value = Env::__callStatic('get', ['database.host']);
   */
  public static function __callStatic($name, $arguments)
  {
    if ($name === 'get') {
      return self::get($arguments[0]);
    }

    throw new \BadMethodCallException(sprintf('Method %s does not exist', $name));
  }
}
