<?php

namespace PulseFrame\Facades;

use PulseFrame\Handlers\RouteHandler;

/**
 * Class Route
 * 
 * @category facades
 * @name Route
 * 
 * This facade provides a simple interface for routing in the application. It forwards static method calls 
 * to an instance of the route handler, allowing you to define routes using intuitive syntax.
 */
class Route
{
  /**
   * Get an instance of the router handler.
   *
   * @category facades
   * 
   * @return mixed The router instance.
   *
   * This protected function retrieves an instance of the router from the route handler singleton. 
   * It ensures that all routing operations are managed by a central router instance.
   * 
   * Example usage:
   * $router = Route::getRouterHandlerInstance();
   */
  protected static function getRouterHandlerInstance()
  {
    return routeHandler::getInstance()->getRouter();
  }

  /**
   * Handle static method calls.
   *
   * @category facades
   * 
   * @param string $method The name of the method being called.
   * @param array $args The arguments passed to the method.
   * @return mixed The result of the router method call.
   *
   * This magic method allows for static method calls to be forwarded to the router instance. 
   * It utilizes the PHP `call_user_func_array` function to dynamically call the corresponding method 
   * on the router instance with the provided arguments.
   * 
   * Example usage:
   * Route::get('/home', 'HomeController@index');
   * Route::post('/login', 'AuthController@login');
   */
  public static function __callStatic($method, $args)
  {
    $router = static::getRouterHandlerInstance();
    return call_user_func_array([$router, $method], $args);
  }
}
