<?php

namespace PulseFrame;

abstract class Job
{
  private $getLastRunTimestamp;
  private $updateLastRunTimestamp;

  private function setTimestampFunctions($getLastRunTimestamp, $updateLastRunTimestamp)
  {
    $this->getLastRunTimestamp = $getLastRunTimestamp;
    $this->updateLastRunTimestamp = $updateLastRunTimestamp;
  }
}