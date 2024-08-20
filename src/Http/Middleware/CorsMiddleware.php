<?php

namespace PulseFrame\Http\Middleware;

use PulseFrame\Middleware;

class CorsMiddleware extends Middleware
{
  public function handle($request, $next)
  {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

    if ($request->getMethod() === 'OPTIONS') {
      exit;
    }

    return $next($request);
  }
}
