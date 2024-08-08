<?php

namespace PulseFrame\Facades;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Log
{
  protected static $logger;

  protected static function getLogger()
  {
    if (!isset(self::$logger)) {
      $logFileName = date('Y-m-d') . '.log';
      $logFilePath = $_ENV['storage_path'] . '/logs/' . $logFileName;
      self::$logger = new Logger(Env::get("app.name"));

      $handler = new StreamHandler($logFilePath, Logger::ERROR);

      $formatter = new LineFormatter("%datetime% %channel%.%level_name%: %message%\n%context% %extra%\n", "Y-m-d H:i:s", true, true);
      $formatter->includeStacktraces(true);
      $handler->setFormatter($formatter);

      self::$logger->pushHandler($handler);
    }

    return self::$logger;
  }

  protected static function formatContext($context)
  {
    if (is_array($context)) {
      $formattedContext = '';
      foreach ($context as $key => $value) {
        $formattedContext .= "$key $value\n";
      }
      return $formattedContext;
    } else {
      return $context;
    }
  }

  public static function Error($message, $context = [])
  {
    $formattedContext = self::formatContext($context);
    self::getLogger()->error("$message\n$formattedContext");
  }

  public static function Exception(\Throwable $e)
  {
    $context = [
      'Exception' => $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(),
      'Trace' => $e->getTraceAsString(),
    ];

    $formattedContext = self::formatContext($context);
    self::Error('Exception occurred:', $formattedContext);
  }
}
