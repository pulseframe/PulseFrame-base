<?php

namespace PulseFrame\Model;

class Model
{
  public $table;
  public $primaryKey = 'id';
  public $timestamps = true;
  public $connection = 'default';
  public $fillable = [];
}
