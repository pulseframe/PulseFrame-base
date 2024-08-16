<?php

namespace PulseFrame\Facades;

/**
 * Class Cookie
 * 
 * @category facades
 * @name Cookie
 * 
 * This class provides methods for managing cookies.
 */
class Cookie
{
  /**
   * Set a cookie.
   *
   * @param string $name The name of the cookie.
   * @param string $value The value of the cookie.
   * @param int $expires The time the cookie expires as a Unix timestamp.
   * @param string $path The path on the server where the cookie will be available.
   * @param string $domain The domain that the cookie is available to.
   * @param bool $secure Whether the cookie should only be transmitted over HTTPS.
   * @param bool $httpOnly Whether the cookie should be accessible only through the HTTP protocol.
   */
  public static function set($name, $value, $expires = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true)
  {
    setcookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
  }

  /**
   * Get a cookie value.
   *
   * @param string $name The name of the cookie.
   * @param mixed $default The default value to return if the cookie does not exist.
   * @return mixed The cookie value or the default value.
   */
  public static function get($name, $default = null)
  {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
  }

  /**
   * Delete a cookie.
   *
   * @param string $name The name of the cookie.
   * @param string $path The path on the server where the cookie will be available.
   * @param string $domain The domain that the cookie is available to.
   */
  public static function delete($name, $path = '/', $domain = '')
  {
    if (isset($_COOKIE[$name])) {
      setcookie($name, '', time() - 3600, $path, $domain);
      unset($_COOKIE[$name]);
    }
  }

  /**
   * Check if a cookie exists.
   *
   * @param string $name The name of the cookie.
   * @return bool True if the cookie exists, false otherwise.
   */
  public static function has($name)
  {
    return isset($_COOKIE[$name]);
  }
}
