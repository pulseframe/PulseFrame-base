<?php

namespace PulseFrame\Http\Middleware;

use PulseFrame\Facades\View;
use PulseFrame\Facades\Response;
use PulseFrame\Facades\Request;
use Closure;

class WebMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    $maintenanceFile = $_ENV['storage_path'] . '/framework/maintenance.flag';

    if (file_exists($maintenanceFile)) {
      if (isset($_SESSION['maintenanceUUID'])) {
        $fileUUID = trim(file_get_contents($maintenanceFile));
        if ($_SESSION['maintenanceUUID'] === $fileUUID) {
        } else {
          return View::render('maintenance.twig');
        }
      } else {
        return View::render('maintenance.twig');
      }
    }

    $currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
      if ($currentUrl === '/account/register' || $currentUrl === '/account/login') {
        $redirect = Request::Query('redirect_to');
        if ($redirect) {
          return Response::Redirect($redirect);
        } else {
          return Response::Redirect("home");
        }
      }
      return $next($request);
    }
    return $next($request);
  }
}
