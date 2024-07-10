<?php

namespace PulseFrame\Methods\Database;

use PulseFrame\Facades\Database;

class Execute
{
  public static function handle($model, $sql, $params = [])
  {
    $db = Database::getConnection($model);
    $statement = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $statement->bindValue(is_numeric($key) ? $key+1 : $key, $value);
    }
    return $statement->execute();
  }
}
