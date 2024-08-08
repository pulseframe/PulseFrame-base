<?php

namespace PulseFrame\Http\Middleware;

use PulseFrame\Facades\Request;
use PulseFrame\Facades\Response;
use Closure;

class ApiMiddleware
{
  public function handle(Request $request, Closure $next)
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
      return Response::JSON('error', 'Rate limit exceeded.');
    }

    return $next($request);
  }
}
