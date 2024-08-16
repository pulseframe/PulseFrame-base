<?php

namespace PulseFrame\Http\Middleware;

use PulseFrame\Middleware;
use PulseFrame\Facades\Response;
use PulseFrame\Facades\Database;
use PulseFrame\Facades\Session;

class AuthMiddleware extends Middleware
{
  public function handle($request, $next)
  {
    if (Session::get('loggedin') !== null && Session::get('loggedin') === true) {
      $user = Database::find("UsersModel", Session::get('email'));
      if ($user === false || Session::get('name') !== $user['name'] || Session::get('email') !== $user['email'] || Session::get('role') !== $user['role']) {
        Session::set('name', $user['name']);
        Session::set('email', $user['email']);
        Session::set('role', $user['role']);
      }

      if (Session::get('password_last_changed') !== $user['password_last_changed']) {
        $this->logout();
      }

      return $next($request);
    } else {
      $currentUrl = urlencode($_SERVER['REQUEST_URI']);
      return Response::Redirect("/account/login?redirect_to=$currentUrl");
    }
  }

  private function logout()
  {
    Session::flush();
    $currentUrl = urlencode($_SERVER['REQUEST_URI']);
    return Response::Redirect("/account/login?redirect_to=$currentUrl");
  }
}
