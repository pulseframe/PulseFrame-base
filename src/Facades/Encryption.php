<?php

namespace PulseFrame\Facades;

use PulseFrame\Facades\Env;

class Encryption
{
  /**
   * Get the encryption key from the environment variables.
   *
   * @return string The encryption key.
   * @throws Exception If the key is not set.
   */
  private static function getKey()
  {
    $key = Env::Get('app.key') ?? null;

    if (!$key) {
      throw new \Exception('Encryption key not set in environment.');
    }

    return $key;
  }

  /**
   * Encrypt a given value.
   *
   * @param string $value The value to encrypt.
   * @return string The encrypted value.
   * @throws Exception If encryption fails.
   */
  public static function encrypt($value)
  {
    $key = self::getKey();
    $method = 'AES-256-CBC';
    $ivSize = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivSize);

    $encrypted = openssl_encrypt($value, $method, $key, 0, $iv);

    if ($encrypted === false) {
      throw new \Exception('Encryption failed.');
    }

    return base64_encode($iv . $encrypted);
  }

  /**
   * Decrypt a given value.
   *
   * @param string $value The value to decrypt.
   * @return string The decrypted value.
   * @throws Exception If decryption fails.
   */
  public static function decrypt($value)
  {
    $key = self::getKey();
    $method = 'AES-256-CBC';
    $data = base64_decode($value);
    $ivSize = openssl_cipher_iv_length($method);
    $iv = substr($data, 0, $ivSize);
    $encrypted = substr($data, $ivSize);

    $decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);

    if ($decrypted === false) {
      throw new \Exception('Decryption failed.');
    }

    return $decrypted;
  }
}
