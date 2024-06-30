<?php

namespace PulseFrame\Facades;

/**
 * Class Config
 * 
 * @category facades
 * @name Config
 * 
 * This class is responsible for managing configuration files and retrieving configuration values. 
 * It loads configuration data from PHP files, caches the loaded configurations, and provides methods 
 * to retrieve specific configuration settings.
 */
class Config
{
  private static $configPath = ROOT_DIR . 'config/';
  private static $configCache = [];

  /**
   * Retrieve a configuration value from a specific configuration file.
   *
   * @category facades
   * 
   * @param string $configFile The name of the configuration file (without extension).
   * @param string|null $key The key of the configuration value to retrieve (optional).
   * @return mixed The configuration value, or the entire configuration array if no key is specified.
   *
   * This function checks if the configuration file has already been loaded and cached. If not, it loads 
   * the configuration file from the specified path and caches it. It then retrieves the requested 
   * configuration value by key or returns the entire configuration array if no key is specified. 
   * Exceptions are thrown if the configuration file or key does not exist.
   * 
   * Example usage:
   * $dbHost = Config::get('database', 'host');
   * $databaseConfig = Config::get('database');
   */
  public static function get($configFile, $key = null)
  {
    if (isset(self::$configCache[$configFile])) {
      $config = self::$configCache[$configFile];
    } else {
      $filePath = self::$configPath . $configFile . '.php';

      if (!file_exists($filePath)) {
        throw new \Exception("Configuration file {$filePath} not found.");
      }

      $config = require $filePath;
      self::$configCache[$configFile] = $config;
    }

    if ($key === null) {
      return $config;
    }

    if (!array_key_exists($key, $config)) {
      throw new \Exception("Key '{$key}' not found in configuration file.");
    }

    return $config[$key];
  }
}
