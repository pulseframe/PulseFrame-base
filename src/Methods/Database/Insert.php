<?php

namespace PulseFrame\Methods\Database;

use PulseFrame\Facades\Database;

class Insert
{
  private static $result;

  public static function handle($model, $attributes, $conflictColumns = [], $updateFields = [])
  {
    $instance = Database::getModelInstance($model);
    $fillable = $instance->fillable;

    $nonFillableFields = array_diff_key($attributes, array_flip($fillable));
    if (!empty($nonFillableFields)) {
      throw new \Exception("Attempting to insert non-fillable fields: " . implode(', ', array_keys($nonFillableFields)));
    }

    $fields = array_keys($attributes);
    $placeholders = array_map(function ($field) { return ':' . $field; }, $fields);

    $sql = "INSERT INTO \"" . $instance->table . "\" (" . implode(", ", array_map(function($field) { return "\"" . $field . "\""; }, $fields)) . ") VALUES (" . implode(", ", $placeholders) . ")";

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
      self::$result = Database::Query($instance, $sql, $params);
    } catch (\Exception $e) {
      throw new \Exception("Failed to insert record into $model: " . $e->getMessage());
    }

    return self::$result;
  }
}
