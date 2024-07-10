<?php

namespace PulseFrame\Methods\Database;

use PulseFrame\Facades\Database;

class All
{
  public static function handle($model, $whereClause = null)
  {
    $instance = Database::getModelInstance($model);
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
  
    return Database::Query($instance, $sql, $attributes, true);
  }
}