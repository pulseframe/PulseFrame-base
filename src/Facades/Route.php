<?php

namespace PulseFrame\Facades;

use PulseFrame\Http\Handlers\RouteHandler;

/**
 * Class Route
 *
 * @category facades
 *
 * This facade provides a simple interface for routing in the application. It forwards static method calls
 * to an instance of the route handler, allowing you to define routes using intuitive syntax.
 */
class Route
{
  /**
   * Get an instance of the router handler.
   *
   * @return mixed The router instance.
   */
  public static function getRouterHandlerInstance()
  {
    return RouteHandler::getInstance()->getRouter();
  }

  /**
   * Handle static method calls.
   *
   * @param string $method The name of the method being called (get, post, etc.).
   * @param array $args The arguments passed to the method.
   * @return mixed The result of the router method call.
   */
  public static function __callStatic($method, $args)
  {
    $router = static::getRouterHandlerInstance();

    if (isset($args[1]) && is_string($args[1]) && strpos($args[1], '@') !== false) {
      $args[1] = RouteHandler::getInstance()->resolveControllerAction($args[1]);
    }

    return call_user_func_array([$router, $method], $args);  
  }

  /**
   * Resolve controller action from 'Controller@method' string format to [Controller::class, 'method'] array format.
   *
   * @param string $action The controller action in 'Controller@method' format.
   * @return array|false The resolved controller action array or false if resolution fails.
   */
  protected static function resolveControllerAction($action)
  {
    if (strpos($action, '@') !== false) {
      list($controller, $method) = explode('@', $action);
      $controller = '\\App\\Http\\Controllers\\' . $controller;
      if (class_exists($controller)) {
        return [$controller, $method];
      }
    }
    return false;
  }
}
