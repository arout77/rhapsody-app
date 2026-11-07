<?php

declare(strict_types=1);

/**
 * Composer pre-update-cmd hook: puts the site into maintenance mode while `composer update` runs.
 *
 * Why this is a script and not a plain "Class::method" entry in composer.json:
 * `composer create-project` fires pre-update-cmd on the very first install, before vendor/ exists, so
 * Rhapsody's UpdateHooks class cannot be autoloaded yet and Composer would print an
 * "is not autoloadable, can not call pre-update-cmd script" warning on every fresh project.
 * On that first run there is no live site to protect, so this script does nothing.
 * On later `composer update` runs it hands over to the framework (Rhapsody\Core\Composer\UpdateHooks);
 * the matching post-update-cmd hook in composer.json turns maintenance mode off again.
 */

$basePath = dirname(__DIR__);
$autoload = $basePath . '/vendor/autoload.php';

// First install (composer create-project): nothing is deployed yet.
if (! is_file($autoload)) {
    exit(0);
}

require $autoload;

$hooks = 'Rhapsody\\Core\\Composer\\UpdateHooks';

// An older core without the helper: skip quietly rather than break the update.
if (! class_exists($hooks) || ! method_exists($hooks, 'enableMaintenance')) {
    exit(0);
}

$hooks::enableMaintenance($basePath);
echo "Maintenance mode ON - updating...\n";
