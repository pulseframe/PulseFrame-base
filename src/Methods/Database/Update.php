<?php

namespace PulseFrame\Methods\Database;

use PulseFrame\Facades\Database;

class Update
{
  public static function handle($model, $id, $attributes)
  {
    $instance = Database::getModelInstance($model);
    $fillable = $instance->fillable;

    $nonFillableFields = array_diff_key($attributes, array_flip($fillable));
    if (!empty($nonFillableFields)) {
      throw new \Exception("Attempting to insert non-fillable fields: " . implode(', ', array_keys($nonFillableFields)));
    }

    $fields = array_keys($attributes);
    $placeholders = array_map(function ($field) { return $field . " = :" . $field; }, $fields);

    $sql = "UPDATE " . $instance->table . " SET " . implode(", ", $placeholders) . " WHERE " . $instance->primaryKey . " = :id";

    $params = [":id" => $id];
    foreach ($attributes as $key => $value) {
      $params[':' . $key] = $value;
    }

    return Database::Query($instance, $sql, $params);
  }
}