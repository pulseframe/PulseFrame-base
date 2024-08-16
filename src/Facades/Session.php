<?php

namespace PulseFrame\Facades;

/**
 * Class Session
 * 
 * @category facades
 * @name Session
 * 
 * This class provides methods for managing session data.
 */
class Session
{
  /**
   * Start a new session if one isn't already started.
   */
  public static function start()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'use_strict_mode' => true
      ]);
    }
  }

  /**
   * Retrieve a value from the session.
   *
   * @param string $key The key of the session variable to retrieve.
   * @param mixed $default The default value to return if the session variable does not exist.
   * @return mixed The session value or the default value.
   */
  public static function get($key, $default = null)
  {
    self::start();
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
  }

  /**
   * Store a value in the session.
   *
   * @param string $key The key of the session variable to store.
   * @param mixed $value The value to store in the session.
   */
  public static function set($key, $value)
  {
    self::start();
    $_SESSION[$key] = $value;
  }

  /**
   * Remove a value from the session.
   *
   * @param string $key The key of the session variable to remove.
   */
  public static function forget($key)
  {
    self::start();
    unset($_SESSION[$key]);
  }

  /**
   * Check if a session variable exists.
   *
   * @param string $key The key of the session variable to check.
   * @return bool True if the session variable exists, false otherwise.
   */
  public static function has($key)
  {
    self::start();
    return isset($_SESSION[$key]);
  }

  /**
   * Flash a value to the session for the next request.
   *
   * @param string $key The key of the session variable to flash.
   * @param mixed $value The value to flash to the session.
   */
  public static function flash($key, $value)
  {
    self::start();
    $_SESSION['flash'][$key] = $value;
  }

  /**
   * Retrieve and delete a flashed value from the session.
   *
   * @param string $key The key of the flashed session variable to retrieve.
   * @param mixed $default The default value to return if the flashed variable does not exist.
   * @return mixed The flashed value or the default value.
   */
  public static function old($key, $default = null)
  {
    self::start();
    $value = isset($_SESSION['flash'][$key]) ? $_SESSION['flash'][$key] : $default;
    unset($_SESSION['flash'][$key]);
    return $value;
  }

  /**
   * Retrieve all session data.
   *
   * @return array The session data.
   */
  public static function all()
  {
    self::start();
    return $_SESSION;
  }

  /**
   * Destroy the session.
   */
  public static function flush()
  {
    self::start();
    $_SESSION = [];
    session_destroy();
  }
}
