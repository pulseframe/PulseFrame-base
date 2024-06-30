<?php

namespace PulseFrame\Handlers;

use PulseFrame\Facades\View;
use PulseFrame\Facades\Env;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class exceptionHandler
 * 
 * @category handlers
 * @name exceptionHandler
 * 
 * This class is responsible for handling application errors and exceptions. It captures exceptions using Sentry, 
 * and displays appropriate error views. It covers various types of exceptions and provides methods to 
 * initialize Sentry and render error views.
 */
class ExceptionHandler
{

  public static function initialize()
  {
    self::initializeSentry(Env::get('sentry_dsn'));

    // Set the exception and error handlers
    set_exception_handler([self::class, 'handle']);
    set_error_handler([self::class, 'handleErrors']);
  }

  /**
   * Initialize Sentry for error tracking.
   *
   * @category handlers
   * 
   * @param string $dsn The Sentry DSN (Data Source Name).
   * 
   * This static function initializes Sentry with the provided DSN and sets the sample rates for traces and profiles.
   * 
   * Example usage:
   * errorHandler::initializeSentry('your-sentry-dsn');
   */
  public static function initializeSentry($dsn)
  {
    \Sentry\init([
      'dsn' => $dsn,
      'traces_sample_rate' => 1.0,
      'profiles_sample_rate' => 1.0,
    ]);
  }

  /**
   * Handle all types of exceptions and errors.
   *
   * @category handlers
   * 
   * @param \Throwable $e The thrown exception or error.
   * 
   * This static function captures exceptions using Sentry and renders appropriate error views based on the type of exception.
   * 
   * Example usage:
   * errorHandler::handle(new \Exception('Something went wrong!'));
   */
  public static function handle(\Throwable $e)
  {
    \Sentry\captureException($e);

    if ($e instanceof HttpExceptionInterface) {
      self::renderErrorView(
        $e->getStatusCode(),
        $e->getMessage(),
        method_exists($e, 'getErrors') ? $e->getErrors() : null
      );
    } elseif ($e instanceof \Exception) {
      self::renderErrorView(500, 'An internal server error occurred.', $e->getMessage());
    } elseif ($e instanceof \RuntimeException) {
      self::renderErrorView(500, 'An internal server error occurred.', $e->getMessage());
    } else {
      self::renderErrorView(500, 'An unexpected error occurred.', $e->getMessage());
    }
  }

  /**
   * Handle PHP errors.
   *
   * @category handlers
   * 
   * @param int $severity The severity of the error.
   * @param string $message The error message.
   * @param string $file The filename where the error occurred.
   * @param int $line The line number where the error occurred.
   * @return bool Always returns false to indicate that standard PHP error handling should proceed.
   * 
   * This static function converts PHP errors into ErrorException and captures them using Sentry.
   * 
   * Example usage:
   * set_error_handler([errorHandler::class, 'handleErrors']);
   */
  public static function handleErrors($severity, $message, $file, $line)
  {
    if (!(error_reporting() & $severity)) {
      return false;
    }

    $exception = new \ErrorException($message, 0, $severity, $file, $line);
    \Sentry\captureException($exception);

    throw $exception;
  }

  /**
   * Render an error view.
   *
   * @category handlers
   * 
   * @param int $statusCode The HTTP status code.
   * @param string $message The error message.
   * @param mixed $exception Additional exception details (optional).
   * 
   * This protected static function renders an error view using the View facade. It catches and handles exceptions 
   * that might occur while rendering the error page.
   * 
   * Example usage:
   * errorHandler::renderErrorView(500, 'Internal Server Error');
   */
  protected static function renderErrorView($statusCode, $message, $exception = null)
  {
    while (ob_get_level()) {
      ob_end_clean();
    }

    try {
      echo View::render('error.twig', ['status' => $statusCode, 'message' => $message, 'exception' => $exception]);
    } catch (\Exception $renderException) {
      echo "An error occurred while rendering the error page.\n" . $renderException->getMessage();
    }

    exit;
  }
}
