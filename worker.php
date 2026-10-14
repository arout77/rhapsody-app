<?php
/**
 * RoadRunner persistent worker entry point.
 *
 * Everything above the while() loop runs ONCE for the life of this worker
 * process — the direct analog of bootstrap.php (boot-once) vs. index.php
 * (per-request) under classic PHP, just kept alive across many requests
 * instead of one.
 *
 * ErrorHandler::register() is called once for the worker. Per-request
 * HttpExceptions are rendered inside RoadRunnerKernel::handle() via
 * ErrorHandler::renderErrorContent() (no exit()). The global handler
 * remains a last-resort safety net for throwables that escape the loop.
 */

define('ROOT_DIR', dirname(__FILE__));
define('RHAPSODY_HTTP_WORKER', true);

require ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/vendor/arout/rhapsody-core/src/helpers.php';

use Nyholm\Psr7\Factory\Psr17Factory;
use Rhapsody\Core\Kernel;
use Rhapsody\RoadRunner\RoadRunnerKernel;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker as RRWorker;

// ---- BOOT ONCE ----
try {
    $repository = Dotenv\Repository\RepositoryBuilder::createWithDefaultAdapters()
        ->addAdapter(Dotenv\Repository\Adapter\PutenvAdapter::class)
        ->make();

    $dotenv = Dotenv\Dotenv::create($repository, ROOT_DIR);
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    die('Could not find .env file. Please ensure it exists in the project root: ' . ROOT_DIR);
}

$config    = require ROOT_DIR . '/config/config.php';
$container = require ROOT_DIR . '/vendor/arout/rhapsody-core/src/bootstrap.php';
$kernel    = new Kernel($container, $config);

\Rhapsody\Core\ErrorHandler::register($config);

// So ErrorHandler::renderProductionError()'s existing container-Twig-reuse
// path (checks $GLOBALS['container']) finds the same singleton Twig
// instance instead of falling back to building an ad hoc one.
$GLOBALS['container'] = $container;

$psr17Factory     = new Psr17Factory();
$roadRunnerKernel = new RoadRunnerKernel($kernel, $psr17Factory, ROOT_DIR, $config);

$worker     = RRWorker::create();
$psr7Worker = new PSR7Worker($worker, $psr17Factory, $psr17Factory, $psr17Factory);

// ---- PER REQUEST ----
while ($psrRequest = $psr7Worker->waitRequest()) {
    try {
        $psr7Worker->respond($roadRunnerKernel->handle($psrRequest));
    } catch (\Throwable $e) {
        // A failure in the bridging itself (not a normal request exception,
        // which RoadRunnerKernel::handle() already caught) — report it to
        // RoadRunner rather than letting it kill the worker silently.
        $psr7Worker->getWorker()->error((string) $e);
    }
}
