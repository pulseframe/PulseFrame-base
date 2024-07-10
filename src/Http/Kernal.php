<?php
namespace PulseFrame\Http;

use PulseFrame\Http\Handlers\RegistryHandler;

class Kernal {
  public static function initialize() {
    (new RegistryHandler())->initialize();
  }
}