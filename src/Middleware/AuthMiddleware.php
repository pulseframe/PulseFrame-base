<?php

namespace PulseFrame\middleware;

use PulseFrame\Facades\Database;
use Closure;
use Illuminate\Http\Request;

class authMiddleware
{
  public function handle(Request $request, Closure $next)
  {
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
      $user = Database::find("usersModel", $_SESSION['email']);

      if ($user === false || $_SESSION['name'] !== $user['name'] || $_SESSION['email'] !== $user['email'] || $_SESSION['role'] !== $user['role']) {
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
      }

      if ($_SESSION['password_last_changed'] !== $user['password_last_changed']) {
        $this->logout();
      }

      return $next($request);
    } else {
      header("Location: /account/login");
      exit;
    }
  }
  private function logout()
  {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }
    session_destroy();
    header("Location: /account/login");
    exit;
  }
}
