<?php

namespace PulseFrame\Methods\Database;

use PulseFrame\Facades\Database;

class CountByColumn
{
  public static function handle($model, $id, $attributes = null)
  {
    $instance = Database::getModelInstance($model);
    
    if ($id !== null) {
      $query = "SELECT COUNT(*) AS count FROM " . $instance->table . " WHERE " . $instance->primaryKey . " = :id";
      $params = [":id" => $id];
    } else {
      if (empty($attributes) || !is_array($attributes)) {
        throw new InvalidArgumentException('Attributes must be a non-empty array when $id is null.');
      }

      $whereClauses = [];
      $params = [];
      foreach ($attributes as $key => $value) {
        $whereClauses[] = "$key = :$key";
        $params[":$key"] = $value;
      }
      $query = "SELECT COUNT(*) AS count FROM " . $instance->table . " WHERE " . implode(" AND ", $whereClauses);
    }

    $result = Database::query($instance, $query, $params);

    return (int) $result['count'];
  }
}