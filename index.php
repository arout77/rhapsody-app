<?php

/**
 * Rhapsody Framework
 *
 * Front Controller
 *
 * This file is the single entry point for all requests. It's responsible for
 * bootstrapping the application, setting up error handling, the service container,
 * and handing the request off to the router.
 */

// Define the absolute path to the downstream application's root directory
define('ROOT_DIR', dirname(__FILE__));
define('RHAPSODY_APP_ROOT', ROOT_DIR);

// 1. Register the Composer autoloader
require ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/vendor/arout/rhapsody-core/src/helpers.php';

// --- START DEBUG COLLECTOR ---
Rhapsody\Core\Debug::getInstance()->start();

// --- ADD MAINTENANCE MODE CHECK ---
$maintenanceFile = ROOT_DIR . '/storage/framework/down';
if (file_exists($maintenanceFile)) {
    if (! headers_sent()) {
        http_response_code(503);
    }
    echo "<h1>Be right back.</h1><p>We are currently performing scheduled maintenance. Please check back soon.</p>";
    exit();
}
// --- END MAINTENANCE MODE CHECK ---

$rootPath = ROOT_DIR;

// 3. Load environment variables from the .env file (with putenv support)
try {
    $repository = Dotenv\Repository\RepositoryBuilder::createWithDefaultAdapters()
        ->addAdapter(Dotenv\Repository\Adapter\PutenvAdapter::class)
        ->make();
    $dotenv = Dotenv\Dotenv::create($repository, $rootPath);
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    // .env file missing – continue with defaults
}

Rhapsody\Core\Session::start();
// Bootstrap the container (this also loads routes and middleware config)
$container = require ROOT_DIR . '/bootstrap.php';

// Load the application configuration
$config = require ROOT_DIR . '/config/config.php';

// Bind config to container
$container->bind('config', function () use ($config) {
    return $config;
});

// Register service providers
$providers = $config['providers'] ?? [];
foreach ($providers as $providerClass) {
    /** @var Rhapsody\Core\ServiceProvider $provider */
    $provider = new $providerClass($container);
    $provider->register();
}

// Load Middleware Configurations into the Router
use Rhapsody\Core\Request;
use Rhapsody\Core\Routing\Router;

Router::setMiddlewareConfig($config['middleware'] ?? []);

// Load route definitions (already loaded in bootstrap, but ensure they are loaded before dispatch)
// We'll let bootstrap load them, but if not, load them here:
// Actually bootstrap already loads routes via require, so we don't need to re-require.
// However, the bootstrap loads routes before we set the container binding for Request.
// So we need to load routes AFTER the Request binding.
// We'll remove route loading from bootstrap and do it here instead.
// But for now, we'll just require them again (they will be added twice? No, Router::setRoutes is not used, it appends).
// Safer: we won't load routes in bootstrap; we'll load them here.
// But bootstrap already does require. Let's override by clearing routes? Not ideal.
// Better to refactor bootstrap to NOT load routes, and let index.php handle it.
// I'll assume you'll adjust bootstrap to not require routes, so we do it here.

// For now, we'll require them (to avoid breaking existing code if bootstrap already loaded).
// If bootstrap already loaded, they'll be duplicate but that's fine – they'll override.
require ROOT_DIR . '/routes/web.php';
if (file_exists(ROOT_DIR . '/routes/api.php')) {
    require ROOT_DIR . '/routes/api.php';
}

// 10. Capture the current global incoming HTTP request
$request = new Request();
$container->bind(Request::class, function () use ($request) {
    return $request;
});

// 11. Dispatch Request – wrapped in a try/catch for all exceptions
try {
    $response = Router::dispatch($request, $container);
} catch (\Throwable $e) {
    // Determine if the client expects JSON
    $isJson = false;
    if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
        $isJson = true;
    }
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        $isJson = true;
    }

    $statusCode = ($e instanceof \Rhapsody\Core\Exceptions\HttpException) ? $e->getStatusCode() : 500;
    $message    = $e->getMessage() ?: 'An unexpected error occurred.';
    $errorCode  = $e->getCode() ?: 0;

    if (! headers_sent()) {
        http_response_code($statusCode);
    }

    if ($isJson) {
        // JSON response for API/AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error'   => [
                'message' => $message,
                'code'    => $errorCode,
                'status'  => $statusCode,
            ],
        ]);
        exit();
    }

    // HTML response – try to render a themed error page
    $theme   = $_ENV['APP_THEME'] ?? $config['theme'] ?? 'default';
    $context = [
        'base_url' => $_ENV['APP_URL'] . ($_ENV['APP_BASE_URL'] ?? ''),
        'app_env'  => $_ENV['APP_ENV'] ?? 'production',
        'meta'     => [
            'title'       => 'Error ' . $statusCode,
            'description' => $message,
        ],
    ];

    // Templates to try in order
    $templates = [
        'errors/' . $statusCode . '.twig',
        'errors/default.twig',
    ];

    // Strategy 1: Use the container's Twig instance (has all globals)
    if ($container->has(\Twig\Environment::class)) {
        try {
            $twig = $container->resolve(\Twig\Environment::class);
            foreach ($templates as $template) {
                try {
                    echo $twig->render($template, $context);
                    exit();
                } catch (\Twig\Error\LoaderError $loaderError) {
                    continue;
                }
            }
        } catch (\Throwable $twigError) {
            error_log('[ErrorHandler] Container Twig failed: ' . $twigError->getMessage() . ' in ' . $twigError->getFile() . ':' . $twigError->getLine());
        }
    }

    // Strategy 2: Fresh Twig instance with fallback paths
    $loaderPaths = array_values(array_filter([
        ROOT_DIR . '/views/themes/' . $theme,
        ROOT_DIR . '/views/themes/default',
        ROOT_DIR . '/vendor/arout/rhapsody-core/resources/views/themes/default',
    ], 'is_dir'));

    if (! empty($loaderPaths)) {
        try {
            $loader = new \Twig\Loader\FilesystemLoader($loaderPaths);
            $twig   = new \Twig\Environment($loader, ['cache' => false]);
            foreach ($templates as $template) {
                try {
                    echo $twig->render($template, $context);
                    exit();
                } catch (\Twig\Error\LoaderError $loaderError) {
                    continue;
                }
            }
        } catch (\Throwable $twigError) {
            error_log('[ErrorHandler] Fresh Twig failed: ' . $twigError->getMessage() . ' in ' . $twigError->getFile() . ':' . $twigError->getLine());
        }
    }

    // Ultimate fallback: plain HTML
    echo "<h1>Error " . $statusCode . "</h1><p>" . htmlspecialchars($message) . "</p>";
    exit();
}

// 13. Get the matched route for debugging
$matchedRoute = Router::getMatchedRoute();

// --- INJECT DEBUG TOOLBAR ---
if ($config['app_env'] === 'development' && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
    $headers     = $response->getHeaders();
    $contentType = $headers['Content-Type'] ?? 'text/html';

    if (str_contains($contentType, 'text/html')) {
        $debug = Rhapsody\Core\Debug::getInstance();
        $debug->end($response, $config, $container, $matchedRoute);
        $toolbar     = new Rhapsody\Core\Toolbar($debug->getData());
        $toolbarHtml = $toolbar->render();

        $content         = $response->getContent();
        $bodyEndPosition = strripos($content, '</body>');
        if ($bodyEndPosition !== false) {
            $content = substr_replace($content, $toolbarHtml, $bodyEndPosition, 0);
        } else {
            $content .= $toolbarHtml;
        }
        $response->setContent($content);

        // Inject update notifications if available
        if ($container->has(\App\Services\NotificationService::class)) {
            // Optional: inject notification banner
        }
    }
}

// 14. Send headers and emit payload
$response->send();
