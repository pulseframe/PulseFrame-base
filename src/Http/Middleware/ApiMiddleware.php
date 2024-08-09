<?php

namespace PulseFrame\Http\Middleware;

use PulseFrame\Middleware;
use PulseFrame\Facades\Response;

class ApiMiddleware extends Middleware
{
  public function handle($request, $next)
  {
    $maxRequestsPerHour = 100;

    $clientId = $request->ip();

    $key = "rate_limit:$clientId";

    $rateLimitData = isset($_SESSION[$key]) ? $_SESSION[$key] : [
      'requests' => 0,
      'start_time' => time(),
    ];

    $currentTime = time();

    if ($currentTime > $rateLimitData['start_time'] + 3600) {
      $rateLimitData['requests'] = 0;
      $rateLimitData['start_time'] = $currentTime;
    }

    $rateLimitData['requests']++;

    $_SESSION[$key] = $rateLimitData;

    if ($rateLimitData['requests'] > $maxRequestsPerHour) {
      http_response_code(429);
      echo Response::JSON('error', 'Rate limit exceeded.');
      exit;
    }

    return $next($request);
  }
}
