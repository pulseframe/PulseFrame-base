<?php

namespace PulseFrame\Http\Middleware;

use PulseFrame\Facades\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Closure;

class AdminMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    if ($_SESSION['role'] === 'admin') {
      return $next($request);
    } else {
      throw new AccessDeniedHttpException('Forbidden');
    }
  }
}
