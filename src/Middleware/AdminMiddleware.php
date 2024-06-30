<?php

namespace PulseFrame\middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class adminMiddleware
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
