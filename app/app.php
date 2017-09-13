<?php
/**
 * Application Builder
 */
namespace PentagonalProject\Prima\App;

use Composer\Autoload\ClassLoader;
use PentagonalProject\SlimService\Application;

if (!isset($config) && file_exists(__DIR__ .'/../Config.php')) {
    $config = require __DIR__ .'/../Config.php';
}

/**
 * @var array|null $config
 */
$config = isset($config) && is_array($config)
    ? $config
    : null;

if (!is_array($config)) {
    echo 'Invalid Configuration!';
    exit(255);
}

/**
 * @var ClassLoader $vendor
 * @var Application $app
 */
$vendor = require __DIR__ . '/../vendor/autoload.php';
$app    = new Application($config, 'default');

# Require Dependencies
$app->requires([
    __DIR__ .'/Container/Container.php',
    __DIR__ .'/Middleware/Middleware.php',
    __DIR__ .'/Route/Route.php',
]);

# Register App Source
$vendor->addPsr4(__NAMESPACE__ . '\\Source\\', __DIR__ .'/Source/');
$vendor->register();

/**
 * @return ClassLoader
 */
$app['loader'] = function () use ($vendor) : ClassLoader {
    return $vendor;
};

return $app->required(__DIR__ .'/runtime.php');
