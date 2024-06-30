<?php

namespace PulseFrame\Handlers;

use PulseFrame\Facades\View;
use PulseFrame\Facades\Config;
use Illuminate\Routing\Router;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;

/**
 * Class routeHandler
 * 
 * @category handlers
 * @name routeHandler
 * 
 * This class is responsible for handling the routing of the application. It initializes the router, 
 * loads routes and middlewares, and processes incoming HTTP requests to dispatch them to the appropriate 
 * controllers. It also handles various HTTP exceptions and renders appropriate error views.
 */
class RouteHandler
{
  protected static $instance = null;
  protected $router;
  protected static $middlewares = [];
  public static $routeNames = [];

  /**
   * routeHandler constructor.
   *
   * @category handlers
   * 
   * This private constructor initializes the router instance and sets up the middleware.
   * 
   * Example usage:
   * $handler = new routeHandler(); // This will not work because the constructor is private.
   */
  private function __construct()
  {
    $this->initializeRouter();
  }

  /**
   * Get the singleton instance of routeHandler.
   *
   * @category handlers
   * 
   * @return routeHandler The singleton instance.
   *
   * This static function returns the singleton instance of the routeHandler class. 
   * If the instance does not exist yet, it creates a new one.
   * 
   * Example usage:
   * $instance = routeHandler::getInstance();
   */
  public static function getInstance()
  {
    if (null === static::$instance) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  /**
   * Initialize the router and load middlewares.
   *
   * @category handlers
   * 
   * This protected function initializes the router instance and loads middlewares defined in the configuration.
   * 
   * Example usage:
   * $this->initializeRouter();
   */
  protected function initializeRouter()
  {
    self::loadMiddlewaresFromConfig(Config::get('app'));

    $this->router = new Router(new Dispatcher());

    foreach (self::$middlewares as $alias => $class) {
      $this->router->aliasMiddleware($alias, $class);
    }
  }

  /**
   * Load middlewares from the configuration.
   *
   * @category handlers
   * 
   * @param array $config The configuration array.
   * 
   * This protected static function loads middleware definitions from the provided configuration array.
   * 
   * Example usage:
   * self::loadMiddlewaresFromConfig($config);
   */
  protected static function loadMiddlewaresFromConfig(array $config)
  {
    self::$middlewares = $config['middleware'];
  }

  /**
   * Load routes from a file.
   *
   * @category handlers
   * 
   * @param string $filePath The path to the file containing routes.
   * 
   * This protected function loads routes from the specified file and registers them with the router.
   * 
   * Example usage:
   * $this->loadRoutesFromFile('/path/to/routes.php');
   */
  protected function loadRoutesFromFile($filePath)
  {
    $routes = include_once $filePath;

    $routeData = [];

    $routes($this->router);

    $routeCollection = $this->router->getRoutes();

    foreach ($routeCollection as $route) {
      $uri = $route->uri();
      $name = $route->getName();

      if ($name) {
        $routeData[] = ['name' => $name, 'url' => $uri];
      }
    }

    self::$routeNames = $routeData;
  }

  /**
   * Load all application routes.
   *
   * @category handlers
   * 
   * This public function loads all routes for the application by grouping them with appropriate middleware.
   * 
   * Example usage:
   * $this->loadRoutes();
   */
  public function loadRoutes()
  {
    $this->router->group(['middleware' => 'web'], function () {
      $this->loadRoutesFromFile(ROOT_DIR . '/routes/web.php');
    });
    $this->router->group(['prefix' => '/api', 'middleware' => 'api'], function () {
      $this->loadRoutesFromFile(ROOT_DIR . '/routes/api.php');
    });
  }

  /**
   * Get the router instance.
   *
   * @category handlers
   * 
   * @return Router The router instance.
   *
   * This public function returns the router instance.
   * 
   * Example usage:
   * $router = $this->getRouter();
   */
  public function getRouter()
  {
    return $this->router;
  }

  /**
   * Handle an incoming request.
   *
   * @category handlers
   * 
   * @param callable|null $loader The loader function (optional).
   * 
   * This public function handles the incoming HTTP request by dispatching it through the router. 
   * It also catches various HTTP exceptions and renders appropriate error views.
   * 
   * Example usage:
   * $this->handleRequest($loader);
   */
  public function handleRequest($loader = null)
  {
    $request = Request::capture();

    try {
      if (!$loader) {
        throw new \RuntimeException('Loader not set');
      }

      $response = $this->router->dispatch($request);
      $response->send();
    } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
      $this->renderErrorView(404, 'The page you are looking for could not be found.');
    } catch (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e) {
      $this->renderErrorView(405, 'The method you are using is not supported.');
    } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
      $this->renderErrorView(403, 'Access denied.', $e);
    } catch (\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException $e) {
      $this->renderErrorView(401, 'Unauthorized access.', $e);
    } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
      $response = $e->getResponse();
      $response->send();
    } catch (\RuntimeException $e) {
      $this->renderErrorView(500, 'A runtime error occurred.', $e);
    } catch (\Exception $e) {
      $this->renderErrorView(500, 'An internal server error occurred.', $e);
    }
  }

  /**
   * Render an error view.
   *
   * @category handlers
   * 
   * @param int $statusCode The HTTP status code.
   * @param string $message The error message.
   * @param \Exception|null $exception The exception instance (optional).
   *
   * This protected function renders an error view using the View facade. It also captures exceptions using Sentry.
   * 
   * Example usage:
   * $this->renderErrorView(404, 'Page not found');
   */
  protected function renderErrorView($statusCode, $message, $exception = null)
  {
    if (isset($exception)) {
      \Sentry\captureException($exception);
    }

    header("HTTP/1.1 $statusCode Internal Server Error");
    echo View::render('error.twig', ['status' => $statusCode, 'message' => $message, 'exception' => $exception]);
    exit;
  }

  /**
   * Handle dynamic method calls.
   *
   * @category handlers
   * 
   * @param string $method The name of the method being called.
   * @param array $arguments The arguments passed to the method.
   * @return mixed The result of the router method call.
   *
   * This magic method allows for dynamic method calls to be forwarded to the router instance.
   * 
   * Example usage:
   * $this->__call('get', ['/path', 'Controller@method']);
   */
  public function __call($method, $arguments)
  {
    return call_user_func_array([$this->router, $method], $arguments);
  }

  /**
   * Run the router handler.
   *
   * @category handlers
   * 
   * @param callable|null $loader The loader function (optional).
   * 
   * This public static function runs the router handler by loading routes and handling the request.
   * 
   * Example usage:
   * routeHandler::run($loader);
   */
  public static function initialize()
  {
    $instance = self::getInstance();
    $instance->loadRoutes();
    $instance->handleRequest((new View())::$twig);
  }
}
