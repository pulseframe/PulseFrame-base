<?php

namespace PulseFrame;

use PulseFrame\Facades\Request;
use Closure;

abstract class Middleware
{
  abstract public function handle(Request $request, Closure $next);
}
