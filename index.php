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

// 1. Register the Composer autoloader
require ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/vendor/arout/rhapsody-core/src/helpers.php';

// --- START DEBUG COLLECTOR ---
Rhapsody\Core\Debug::getInstance()->start();

// --- ADD MAINTENANCE MODE CHECK ---
$maintenanceFile = ROOT_DIR . '/storage/framework/down';
if (file_exists($maintenanceFile)) {
    http_response_code(503);
    echo "<h1>Be right back.</h1><p>We are currently performing scheduled maintenance. Please check back soon.</p>";
    exit();
}
// --- END MAINTENANCE MODE CHECK ---

$rootPath = ROOT_DIR;

// 3. Load environment variables from the .env file (with putenv support)
try {
    // Create a repository that handles both superglobals ($_ENV/$_SERVER) AND putenv()
    $repository = Dotenv\Repository\RepositoryBuilder::createWithDefaultAdapters()
        ->addAdapter(Dotenv\Repository\Adapter\PutenvAdapter::class)
        ->make();

    $dotenv = Dotenv\Dotenv::create($repository, $rootPath);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    die('Could not find .env file. Please ensure it exists in the project root: ' . $rootPath);
}

// 4. Register Error Handling (Whoops)
$config = require_once $rootPath . '/config/config.php';
Rhapsody\Core\ErrorHandler::register($config);

// 5. Start the session
Rhapsody\Core\Session::start();

// 6. Bootstrap the application and get the service container
$container            = require_once $rootPath . '/bootstrap.php';
$GLOBALS['container'] = $container;

// 7. Use necessary core classes
use Rhapsody\Core\Request;
use Rhapsody\Core\Routing\Router;

// 8. Create the Request object
$request = new Request();

// 9. Load the application routes from cache if available
$routeCachePath = $rootPath . '/storage/cache/routes/routes.php';
if (file_exists($routeCachePath) && $config['app_env'] === 'production') {
    $routes = require_once $routeCachePath;
    Router::setRoutes($routes);
} else {
    require_once $rootPath . '/routes/web.php';
    require_once $rootPath . '/routes/api.php';
}

// 10. Load the middleware configuration and set it on the Router
// Router::setMiddlewareConfig($config);
Router::setMiddlewareConfig($config['middleware']);

// 11. Dispatch the request through the router, passing the container
try {
    $response = Router::dispatch($request, $container);
} catch (Rhapsody\Core\Exceptions\HttpException $e) {
    // Let the error handler take over (it will render a custom error page)
    throw $e;
}

// 12. Handle 404/500 responses if they are returned as Response objects (e.g., from middleware)
if ($response->getStatusCode() === 404) {
    throw new Rhapsody\Core\Exceptions\HttpException(404, 'Page not found');
}
if ($response->getStatusCode() === 500) {
    throw new Rhapsody\Core\Exceptions\HttpException(500, 'Server error');
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
        /** @var \App\Services\NotificationService $notificationService */
        $notificationService = $container->resolve(\App\Services\NotificationService::class);
        $response            = $notificationService->injectBanner($response);
    }
}

// 14. Send the response back to the client
$response->send();
