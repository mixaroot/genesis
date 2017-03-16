<?php
// Load composer classes
require 'vendor/autoload.php';

// Auto load classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $class) . '.php';
    include_once $file;
});

// Run routing
(new lib\RouteConsole)->init($argv);