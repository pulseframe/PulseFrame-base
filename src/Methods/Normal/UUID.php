<?php

namespace PulseFrame\Methods\Normal;

class UUID {
  public static function Generate()
  {
    $data = random_bytes(16);
  
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
      
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }
}