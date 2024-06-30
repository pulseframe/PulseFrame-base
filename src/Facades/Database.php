<?php

namespace PulseFrame\Facades;

use PulseFrame\Facades\Config;
use PulseFrame\Model\Model;
use PDO;

/**
 * Class Database
 * 
 * @category facades
 * @name Database
 * 
 * This class handles the database interactions for models. It establishes a database connection, executes 
 * queries, and manages the retrieval and manipulation of data via the models.
 */
class Database
{
  private static $conn;
  private static $result;

  /**
   * Get the database connection.
   *
   * @category facades
   * 
   * @param Model $model The model for which the connection is being established.
   * @return PDO The PDO connection instance.
   *
   * This protected function initializes the database connection if it has not been already. It retrieves 
   * database configuration settings using the Config facade and establishes a PDO connection. 
   * It throws an exception in case of connection failure.
   * 
   * Example usage:
   * $connection = Database::getConnection(new SomeModel());
   */
  protected static function getConnection($model)
  {
    if (is_object($model) && property_exists($model, 'connection')) {
      $connectionName = $model->connection;
    } elseif (is_string($model)) {
      $connectionName = $model;
    } else {
      throw new \InvalidArgumentException("Invalid model type. Must be an object with a 'connection' property or a string.");
    }

    $databaseConfig = Config::get('database');

    if (!array_key_exists($connectionName, $databaseConfig)) {
      return $connectionName;
    }

    if (!self::$conn) {
      $host = $databaseConfig[$connectionName]['host'];
      $port = $databaseConfig[$connectionName]['port'];
      $username = $databaseConfig[$connectionName]['username'];
      $password = $databaseConfig[$connectionName]['password'];
      $database = $databaseConfig[$connectionName]['database'];
      $driver = $databaseConfig[$connectionName]['driver'];

      try {
        self::$conn = new PDO("$driver:host=$host;port=$port;dbname=$database", $username, $password);
        self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (\PDOException $e) {
        throw new \Exception("Connection failed: " . $e->getMessage());
      }
    }

    return self::$conn;
  }

  /**
   * Get an instance of the specified model.
   *
   * @category facades
   * 
   * @param string $model The name of the model class.
   * @return Model The model instance.
   *
   * This protected function dynamically creates an instance of the specified model. 
   * It throws an exception if the model class does not exist.
   * 
   * Example usage:
   * $modelInstance = Database::getModelInstance('UserModel');
   */
  protected static function getModelInstance($model)
  {
    $className = "\App\models\\" . $model;

    if (!class_exists($className)) {
      throw new \Exception("Model not found: {$model}");
    }

    return new $className();
  }

  /**
   * Execute a query against the database.
   *
   * @category facades
   * 
   * @param Model $model The model instance used for the query.
   * @param string $sql The SQL query string.
   * @param array $params The parameters to bind to the query (optional).
   * @return array The fetched data as an associative array.
   *
   * This protected function executes a SQL query using the PDO connection and retrieves the results. 
   * It binds the provided parameters to the query and returns the fetched records.
   * 
   * Example usage:
   * $results = Database::query($usersModel, "SELECT * FROM users WHERE id = :id", [":id" => 1]);
   */
  public static function query($model, $sql, $params = [], $fetch_all = false)
  {
    $db = self::getConnection($model);
    $statement = $db->prepare($sql);

    foreach ($params as $key => $value) {
      $statement->bindValue($key, $value);
    }

    $statement->execute();
    if ($fetch_all) {
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $statement->fetch(PDO::FETCH_ASSOC);
    }
  }

  /**
   * Retrieve all records from the specified model's table.
   *
   * @category facades
   * 
   * @param string $model The name of the model class.
   * @return array The fetched records as an associative array.
   *
   * This public function retrieves all records from the table associated with the specified model.
   * 
   * Example usage:
   * $allUsers = Database::all('UserModel');
   */
  public static function all($model, $whereClause = null)
  {
    $instance = self::getModelInstance($model);
    $sql = "SELECT * FROM " . $instance->table;
    $attributes = [];

    if ($whereClause !== null) {
      $conditions = [];
      foreach ($whereClause as $key => $value) {
        $conditions[] = "$key = :$key";
        $attributes[":$key"] = $value;
      }
      $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    return self::query($instance, $sql, $attributes, true);
  }

  /**
   * Find a record by its primary key in the specified model's table.
   *
   * @category facades
   * 
   * @param string $model The name of the model class.
   * @param mixed $id The primary key value of the record.
   * @return array The fetched record as an associative array.
   *
   * This public function retrieves a record by its primary key from the table associated with the specified model.
   * 
   * Example usage:
   * $user = Database::find('UserModel', 1);
   */
  public static function find($model, $id = null, $whereClause = null, $attributes = [])
  {
    $instance = self::getModelInstance($model);

    if ($id !== null && $whereClause === null) {
      $sql = "SELECT * FROM " . $instance->table . " WHERE " . $instance->primaryKey . " = :id";
      $attributes = [":id" => $id];
    } else if ($whereClause !== null) {
      $conditions = [];
      foreach ($whereClause as $key => $value) {
        $conditions[] = "$key = :$key";
        $attributes[":$key"] = $value;
      }
      $sql = "SELECT * FROM " . $instance->table . " WHERE " . implode(" AND ", $conditions);
    } else {
      throw new \InvalidArgumentException("Either ID or where clause must be provided.");
    }

    return self::query($instance, $sql, $attributes);
  }

  /**
   * Insert a new record into the specified model's table.
   *
   * @category facades
   * 
   * @param string $model The name of the model class.
   * @param array $attributes The attributes for the new record.
   * @return array The result of the query execution.
   *
   * This public function inserts a new record into the table associated with the specified model. 
   * It validates that only fillable fields are being inserted.
   * 
   * Example usage:
   * $result = Database::insert('UserModel', ['name' => 'John Doe', 'email' => 'john@example.com']);
   */
  public static function insert($model, $attributes, $conflictColumns = [], $updateFields = [])
  {
    $instance = self::getModelInstance($model);
    $fillable = $instance->fillable;

    $nonFillableFields = array_diff_key($attributes, array_flip($fillable));
    if (!empty($nonFillableFields)) {
      throw new \Exception("Attempting to insert non-fillable fields: " . implode(', ', array_keys($nonFillableFields)));
    }

    $fields = array_keys($attributes);
    $placeholders = array_map(function ($field) {
      return ':' . $field;
    }, $fields);

    $sql = "INSERT INTO \"" . $instance->table . "\" (" . implode(", ", array_map(function ($field) {
      return "\"" . $field . "\"";
    }, $fields)) . ") VALUES (" . implode(", ", $placeholders) . ")";

    if (!empty($conflictColumns)) {
      $conflictFormatted = implode(', ', array_map(function ($column) {
        return "\"" . $column . "\"";
      }, $conflictColumns));

      if (!empty($updateFields)) {
        $updatePlaceholders = array_map(function ($field) {
          return "\"" . $field . "\" = excluded.\"" . $field . "\"";
        }, $updateFields);

        $updateFormatted = implode(', ', $updatePlaceholders);
        $sql .= " ON CONFLICT (" . $conflictFormatted . ") DO UPDATE SET " . $updateFormatted;
      } else {
        $sql .= " ON CONFLICT (" . $conflictFormatted . ") DO NOTHING";
      }
    }

    $sql .= " RETURNING *";

    $params = [];
    foreach ($attributes as $key => $value) {
      $params[':' . $key] = $value;
    }

    try {
      self::$result = self::query($instance, $sql, $params);
    } catch (\Exception $e) {
      error_log("Failed to insert record into $model: " . $e->getMessage());
    }

    return self::$result;
  }

  /**
   * Update an existing record in the specified model's table.
   *
   * @category facades
   * 
   * @param string $model The name of the model class.
   * @param mixed $id The primary key value of the record.
   * @param array $attributes The attributes to update.
   * @return array The result of the query execution.
   *
   * This public function updates an existing record in the table associated with the specified model. 
   * It validates that only fillable fields are being updated.
   * 
   * Example usage:
   * $result = Database::update('UserModel', 1, ['name' => 'John Smith']);
   */
  public static function update($model, $id, $attributes)
  {
    $instance = self::getModelInstance($model);
    $fillable = $instance->fillable;

    $nonFillableFields = array_diff_key($attributes, array_flip($fillable));
    if (!empty($nonFillableFields)) {
      throw new \Exception("Attempting to insert non-fillable fields: " . implode(', ', array_keys($nonFillableFields)));
    }

    $fields = array_keys($attributes);
    $placeholders = array_map(function ($field) {
      return $field . " = :" . $field;
    }, $fields);

    $sql = "UPDATE " . $instance->table . " SET " . implode(", ", $placeholders) . " WHERE " . $instance->primaryKey . " = :id";

    $params = [":id" => $id];
    foreach ($attributes as $key => $value) {
      $params[':' . $key] = $value;
    }

    return self::query($instance, $sql, $params);
  }

  /**
   * Delete a record by its primary key from the specified model's table.
   *
   * @category facades
   * 
   * @param string $model The name of the model class.
   * @param mixed $id The primary key value of the record.
   * @return array The result of the query execution.
   *
   * This public function deletes a record by its primary key from the table associated with the specified model.
   * 
   * Example usage:
   * $result = Database::delete('UserModel', 1);
   */
  public static function delete($model, $id)
  {
    $instance = self::getModelInstance($model);
    return self::query($instance, "DELETE FROM " . $instance->table . " WHERE " . $instance->primaryKey . " = :id", ["id" => $id]);
  }

  /**
   * Count the number of records matching the specified condition.
   *
   * @category facades
   * 
   * @param string $model The name of the model class.
   * @param string $column The column to search.
   * @param mixed $value The value to search for.
   * @return int The count of matching records.
   *
   * This public function counts the number of records in the table associated with the specified model 
   * that match the provided condition.
   * 
   * Example usage:
   * $count = Database::countByColumn('UserModel', 'email', 'john@example.com');
   */
  public static function countByColumn($model, $id, $attributes = null)
  {
    $instance = self::getModelInstance($model);

    if ($id !== null) {
      $query = "SELECT COUNT(*) AS count FROM " . $instance->table . " WHERE " . $instance->primaryKey . " = :id";
      $params = [":id" => $id];
    } else {
      if (empty($attributes) || !is_array($attributes)) {
        throw new \InvalidArgumentException('Attributes must be a non-empty array when $id is null.');
      }

      $whereClauses = [];
      $params = [];
      foreach ($attributes as $key => $value) {
        $whereClauses[] = "$key = :$key";
        $params[":$key"] = $value;
      }
      $query = "SELECT COUNT(*) AS count FROM " . $instance->table . " WHERE " . implode(" AND ", $whereClauses);
    }

    $result = self::query($instance, $query, $params);

    return (int) $result['count'];
  }

  /**
   * Execute a prepared statement with bound parameters.
   *
   * @category database
   * 
   * @param string $model The name of the model for which the database connection is required.
   * @param string $sql The SQL query to be executed.
   * @param array $params The parameters to bind to the query.
   * @return bool Returns true on success or false on failure.
   *
   * This public static function prepares and executes an SQL statement using the provided model, 
   * SQL query, and parameters. It binds the parameters to the SQL statement before execution.
   * 
   * Example usage:
   * $result = Database::execute('UserModel', 'SELECT * FROM users WHERE id = :id', ['id' => 1]);
   */
  public static function execute($model, $sql, $params = [])
  {
    $db = self::getConnection($model);
    $statement = $db->prepare($sql);
    foreach ($params as $key => $value) {
      $statement->bindValue(is_numeric($key) ? $key + 1 : $key, $value);
    }
    return $statement->execute();
  }
}
