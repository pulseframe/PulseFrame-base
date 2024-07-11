<?php

namespace PulseFrame;

class Model
{
  public $table;
  public $primaryKey = 'id';
  public $timestamps = true;
  public $connection = 'default';
  public $fillable = [];
}
