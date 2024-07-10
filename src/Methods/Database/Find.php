<?php

namespace PulseFrame\Methods\Database;

use PulseFrame\Facades\Database;

class Find
{
  public static function handle($model, $id = null, $whereClause = null, $attributes = [])
  {
    $instance = Database::getModelInstance($model);

    if ($id !== null && $whereClause === null) {
      $sql = "SELECT * FROM " . $instance->table . " WHERE " . $instance->primaryKey . " = :id";
      $attributes = [":id" => $id];
    }
    else if ($whereClause !== null) {
      $conditions = [];
      foreach ($whereClause as $key => $value) {
          $conditions[] = "$key = :$key";
          $attributes[":$key"] = $value;
      }
      $sql = "SELECT * FROM " . $instance->table . " WHERE " . implode(" AND ", $conditions);
    }
    else {
      throw new \InvalidArgumentException("Either ID or where clause must be provided.");
    }
    try {
      return Database::Query($instance, $sql, $attributes);
    } catch (\Exception $e) {
      throw new \Exception($e);
    }
  }
}