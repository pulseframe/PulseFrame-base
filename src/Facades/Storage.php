<?php

namespace PulseFrame\Facades;

/**
 * Class Storage
 * 
 * @category facades
 * @name Storage
 * 
 * This class provides methods for interacting with storage files.
 */
class Storage
{
  /**
   * Get the base storage path from the environment variables.
   *
   * @return string The base storage path.
   */
  private static function getBasePath()
  {
    // Ensure the storage base path is set in the environment
    if (!isset($_ENV['storage_path'])) {
      throw new \Exception('STORAGE_PATH environment variable is not set.');
    }
    return rtrim($_ENV['storage_path'], '/');
  }

  /**
   * Get the full path to a file in the storage directory.
   *
   * @param string $path The relative path to the file.
   * @return string The full path to the file.
   */
  public static function path($path)
  {
    return self::getBasePath() . '/' . ltrim($path, '/');
  }

  /**
   * Check if a file exists in the storage directory.
   *
   * @param string $path The relative path to the file.
   * @return bool True if the file exists, false otherwise.
   */
  public static function exists($path)
  {
    return file_exists(self::path($path));
  }

  /**
   * Write data to a file in the storage directory.
   *
   * @param string $path The relative path to the file.
   * @param string $data The data to write to the file.
   * @return bool True on success, false on failure.
   */
  public static function put($path, $data)
  {
    return file_put_contents(self::path($path), $data) !== false;
  }

  /**
   * Read data from a file in the storage directory.
   *
   * @param string $path The relative path to the file.
   * @return string|false The file contents or false on failure.
   */
  public static function get($path)
  {
    return file_get_contents(self::path($path));
  }

  /**
   * Delete a file from the storage directory.
   *
   * @param string $path The relative path to the file.
   * @return bool True on success, false on failure.
   */
  public static function delete($path)
  {
    return unlink(self::path($path));
  }
}
