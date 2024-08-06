<?php

namespace PulseFrame\Facades;

use PulseFrame\Facades\Config;
use PulseFrame\Facades\Env;
use PulseFrame\Http\Handlers\RouteHandler;
use Symfony\Component\HttpFoundation\Response;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Exception;

/**
 * Class View
 * 
 * @category facades
 * @name View
 * 
 * This class handles the rendering of Twig templates. It initializes Twig, manages environment configurations, 
 * and integrates with the Vite asset builder for both development and production environments.
 */
class View
{
  public static $twig;
  private $manifest;

  /**
   * View constructor.
   *
   * @category facades
   * 
   * This constructor checks if environment variables are loaded and initializes the Twig environment 
   * if it hasn't been initialized yet.
   * 
   * Example usage:
   * $view = new View();
   */
  public function __construct()
  {
    if (!self::$twig) {
      self::initialize();
    }

    $manifestPath = ROOT_DIR . '/public/assets/.vite/manifest.json';
    if (file_exists($manifestPath)) {
      $this->manifest = json_decode(file_get_contents($manifestPath), true);
    }
  }

  /**
   * Initialize the Twig environment.
   *
   * @category facades
   * 
   * This static function sets up the Twig environment by specifying the template directory and enabling debug mode. 
   * It also adds a debug extension to Twig.
   * 
   * Example usage:
   * View::initialize();
   */
  private static function initialize()
  {
    $loader = new FilesystemLoader(Config::get('view')['twig']['path']);

    self::$twig = new Environment($loader);
    self::$twig->addExtension(new \Twig\Extension\DebugExtension());
  }

  /**
   * Render a Twig template.
   *
   * @category facades
   * 
   * @param string $template The name of the Twig template.
   * @param array $data The data to pass to the template (optional).
   * @return string The rendered HTML content.
   *
   * This static function renders a Twig template with the provided data. It checks if the Vite development 
   * server is running and renders appropriate error messages if required assets are missing.
   * 
   * Example usage:
   * $html = View::render('template.twig', ['key' => 'value']);
   */
  public static function render($template, $data = [])
  {
    $manifest = (new self())->manifest;

    if (!self::$twig) {
      self::initialize();
    }

    $hotFile = $_ENV['storage_path'] . '/hot';
    $isDev = file_exists($hotFile);

    $data['isDev'] = $isDev;

    if ($template === 'error.twig') {
      $data['debug'] = Env::get("app.settings.debug");
    }

    if ($isDev && !self::isViteServerRunning()) {
      $message = "Development server not detected.";
      if (Env::get("app.settings.debug")) {
        $message .= "<br>Hint: Make sure the Vite dev server is running.<br>If this is a mistake, delete the <code>storage/hot</code> file.";
      }
      return self::$twig->render('error.twig', ['status' => "500", 'message' => $message]);
    } elseif (!$isDev && (!isset($manifest) || empty($manifest))) {
      $message = "Manifest file not found";
      if (Env::get("app.settings.debug")) {
        $message .= "<br>Hint: Make sure you have built the project.<br>Not sure? Run <code>npx vite build</code>.";
      }
      return self::$twig->render('error.twig', ['status' => "500", 'message' => $message]);
    } else {
      $view = new self();
      $response = $view->renderView($template, $data);
      return $response->getContent();
    }
  }

  /**
   * Check if Vite development server is running.
   *
   * @category facades
   * 
   * @return bool True if the Vite server is running, otherwise false.
   *
   * This private function checks the availability of the Vite development server by sending a request to 
   * the specified Vite server URL.
   * 
   * Example usage:
   * $isRunning = View::isViteServerRunning();
   */
  private static function isViteServerRunning(): bool
  {
    $viteServerUrl = Env::get("vite_dev");
    $headers = @get_headers($viteServerUrl);

    return $headers && strpos($headers[0], '200') !== false;
  }

  /**
   * Render a view with assets.
   *
   * @category facades
   * 
   * @param string $template The name of the template.
   * @param array $data The data to pass to the template (optional).
   * @return Response The HTTP response.
   *
   * This function renders a Twig template and resolves Vite assets.
   * 
   * Example usage:
   * $response = $view->renderView('template.vue', ['key' => 'value']);
   */
  private function renderView(string $template, array $data = []): Response
  {
    $extension = substr($template, -4);

    if (in_array($extension, ['.vue', '.ts', '.tsx'])) {
      $template = str_replace($extension, '', $template);

      $data['app'] = array_merge([
        'session' => $_SESSION,
        'settings' => Env::get('app.settings'),
        'page' => $template,
        'routes' => RouteHandler::$routeNames
      ], $data);

      if (!$data['isDev']) {
        try {
          $data['assets'] = $this->resolveAssets(Config::get('app', 'entry'));
        } catch (Exception $e) {
          return new Response(
            self::$twig->render('error.twig', [
              'status' => 500,
              'message' => $e->getMessage(),
            ]),
            Response::HTTP_INTERNAL_SERVER_ERROR
          );
        }
      }

      if (isset($_SESSION['password'])) {
        unset($_SESSION['password']);
      }

      $template = 'base.twig';
    }

    return new Response(self::$twig->render($template, $data));
  }

  /**
   * Resolve assets and dependencies.
   *
   * @category facades
   * 
   * @param string $entry The entry point.
   * @return array The resolved assets and dependencies.
   *
   * This function resolves the assets and dependencies for the given entry by processing the manifest file.
   * 
   * Example usage:
   * $assets = $view->resolveAssets('main.js');
   */
  private function resolveAssets(string $entry): array
  {
    if (!isset($this->manifest[$entry])) {
      throw new \RuntimeException("Entry $entry not found in manifest");
    }

    $result = [];
    $stack = [$entry];

    while (!empty($stack)) {
      $currentEntry = array_pop($stack);

      if (!isset($this->manifest[$currentEntry])) {
        continue;
      }

      $entryData = $this->manifest[$currentEntry];

      if (isset($entryData['css'])) {
        foreach ($entryData['css'] as $css) {
          $result[] = $css;
        }
      }

      $result[] = $entryData['file'];

      if (isset($entryData['imports'])) {
        foreach ($entryData['imports'] as $import) {
          $stack[] = $import;
        }
      }

      unset($this->manifest[$currentEntry]);
    }

    return array_unique($result);
  }
}
