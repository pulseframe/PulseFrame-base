<?php

namespace PulseFrame\database;

use PulseFrame\Facades\Database;

abstract class Migration
{
  protected $table;
  protected $connection;

  abstract public function up();
  abstract public function down();

  public function createTable(array $columns)
  {
    $columnsSql = [];
    foreach ($columns as $name => $type) {
      if (strpos($type, 'INT AUTO_INCREMENT') !== false) {
        $type = str_replace('INT AUTO_INCREMENT', 'SERIAL', $type);
      }

      $columnsSql[] = "\"$name\" $type";
    }
    $columnsString = implode(", ", $columnsSql);
    $sql = "CREATE TABLE IF NOT EXISTS \"{$this->table}\" ({$columnsString})";

    Database::Execute('default', $sql);

    //Database::Execute()
    //  ->connection($this->connection)
    //  ->sql($sql)
    //  ->execute();
  }

  public function dropTable()
  {
    $sql = "DROP TABLE IF EXISTS \"{$this->table}\"";
    Database::Execute($this->connection, $sql);
  }
}
