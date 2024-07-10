<?php

namespace PulseFrame\Methods\Database;

use PulseFrame\Facades\Database;

class Delete
{
  public static function handle($model, $id)
  {
    $instance = Database::getModelInstance($model);
    return Database::Query($instance, "DELETE FROM " . $instance->table . " WHERE " . $instance->primaryKey . " = :id", ["id" => $id]);
  }
}
