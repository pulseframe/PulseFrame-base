<?php

namespace PulseFrame\Facades;

use PulseFrame\Http\Handlers\RouteHandler;
use PulseFrame\Facades\Route;

class Response {
  public static function JSON($status, $message, $code = null) {
    return json_encode(['status' => $status, 'message' => $message, 'code' => $code]);
  }

  public static function Redirect($routeName, $parameters = [], $status = 302, $headers = [])
  {
    $router = Route::getRouterHandlerInstance();

    $route = self::findRouteByName($router, $routeName);

    if ($route) {
      $url = $route['url'];

      self::performRedirect($url, $status, $headers);
    } else {
      throw new \InvalidArgumentException("Route '{$routeName}' not found.");
    }
  }

  protected static function findRouteByName($router, $routeName)
  {
    $routeNames = RouteHandler::$routeNames;

    foreach ($routeNames as $route) {
      if ($route['name'] === $routeName) {
        return $route;
      }
    }

    return null;
  }

  protected static function performRedirect($url, $status = 302, $headers = [])
  {
    http_response_code($status);

    foreach ($headers as $key => $value) {
      header("{$key}: {$value}");
    }

    header("Location: {$url}");
    exit;
  }
}