<?php

namespace PulseFrame\middleware;

use PulseFrame\Facades\View;
use Closure;
use Illuminate\Http\Request;

class webMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'use_strict_mode' => true
      ]);
    }

    if ($this->isMaintenanceMode()) {
      return View::render('maintenance.twig');
    }

    $currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
      if ($currentUrl === '/account/register' || $currentUrl === '/account/login') {
        header('Location: /');
        exit();
      }
      return $next($request);
    }
    return $next($request);
  }

  protected function isMaintenanceMode()
  {
    return file_exists($_ENV['storage_path'] . '/framework/maintenance.flag');
  }
}
