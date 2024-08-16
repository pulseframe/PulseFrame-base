<?php

namespace PulseFrame\Http\Middleware;

use PulseFrame\Middleware;
use PulseFrame\Facades\Session;
use PulseFrame\Exceptions\AccessForbiddenException;

class AdminMiddleware extends Middleware
{
  public function handle($request, $next)
  {
    if (Session::get('role') === 'admin') {
      return $next($request);
    } else {
      throw new AccessForbiddenException("You are not authorized to access this resource.");
    }
  }
}
