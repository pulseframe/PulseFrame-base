<?php

namespace PulseFrame\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
      return new JsonResponse(['error' => 'Rate limit exceeded.'], 429);
    }

    return $next($request);
  }
}
