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
$container            = require ROOT_DIR . '/vendor/arout/rhapsody-core/src/bootstrap.php';
$GLOBALS['container'] = $container;

// 7. Use necessary core classes
use Rhapsody\Core\Request;

// 8. Create the Request object
$request = new Request();

// 9. Hand the request to the Kernel and send the resulting response.
// Route loading and middleware configuration now happen once, at boot,
// inside bootstrap.php — not here, and not per-request.
$kernel   = new Rhapsody\Core\Kernel($container, $config);
$response = $kernel->handle($request);
$response = $kernel->terminate($request, $response);
$response->send();
