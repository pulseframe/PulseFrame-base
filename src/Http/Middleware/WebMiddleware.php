<?php

namespace PulseFrame\Http\Middleware;

use PulseFrame\Middleware;
use PulseFrame\Facades\View;
use PulseFrame\Facades\Response;
use PulseFrame\Facades\Request;

class WebMiddleware extends Middleware
{
  public function handle($request, $next)
  {
    $maintenanceFile = $_ENV['storage_path'] . '/framework/maintenance.flag';

    if (file_exists($maintenanceFile)) {
      if (isset($_SESSION['maintenanceUUID'])) {
        $fileUUID = trim(file_get_contents($maintenanceFile));
        if ($_SESSION['maintenanceUUID'] === $fileUUID) {
        } else {
          return View::render('maintenance');
        }
      } else {
        return View::render('maintenance');
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
