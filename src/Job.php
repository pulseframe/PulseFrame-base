<?php

namespace PulseFrame;

abstract class Job
{
  private $getLastRunTimestamp;
  private $updateLastRunTimestamp;

  public function setTimestampFunctions($getLastRunTimestamp, $updateLastRunTimestamp)
  {
    $this->getLastRunTimestamp = $getLastRunTimestamp;
    $this->updateLastRunTimestamp = $updateLastRunTimestamp;
  }
}