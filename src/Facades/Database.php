<?php

namespace PulseFrame\Facades;

use PulseFrame\Models\Model as ModelAlias;
use PDO;
use PDOException;

class Database
{
  private static $conn = [];

  public static function getConnection($model)
  {
    if (is_string($model)) {
      $connectionName = $model;
    } elseif (is_object($model) && property_exists($model, 'connection')) {
      $connectionName = $model->connection;
    } else {
      throw new \InvalidArgumentException("Invalid model type. Must be an object with a 'connection' property or a string.");
    }
    $databaseConfig = Config::get('database');
  
    if (!array_key_exists($connectionName, $databaseConfig)) {
      return $connectionName;
    }
  
    if (!self::$conn) {
      $host = $databaseConfig[$connectionName]['host'];
      $username = $databaseConfig[$connectionName]['username'];
      $password = $databaseConfig[$connectionName]['password'];
      $database = $databaseConfig[$connectionName]['database'];
      $port = $databaseConfig[$connectionName]['port'];
      $driver = $databaseConfig[$connectionName]['driver'];
  
      try {
        self::$conn = new PDO("$driver:host=$host;dbname=$database;port=$port", $username, $password);
        self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
        throw new \Exception("Connection failed: " . $e->getMessage());
      }
    }
    return self::$conn;
  }

  public static function getModelInstance($model)
  {
    $className = "\\App\\Models\\" . $model;

    if (!class_exists($className)) {
      throw new \Exception("Model not found: {$model}");
    }

    return new $className();
  }

  public static function __callStatic($method, $args) {
    $methodClass = "\\PulseFrame\\Methods\\Database\\" . ucfirst($method);
    if (class_exists($methodClass) && method_exists($methodClass, 'handle')) {
      return call_user_func_array([$methodClass, 'handle'], $args);
    } else {
      throw new \BadMethodCallException("Method {$method} does not exist.");
    }
  }
}
