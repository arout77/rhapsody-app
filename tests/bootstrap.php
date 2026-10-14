<?php

// Define the root directory specifically for the testing environment
define('ROOT_DIR', dirname(__DIR__));

// Load Composer autoloader
require_once ROOT_DIR . '/vendor/autoload.php';

// (Optional) Load your .env.testing file here if you use separate databases for tests
