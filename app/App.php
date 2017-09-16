<?php
/**
 * MIT License
 *
 * Copyright (c) 2017, Pentagonal
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

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

# Register App Source & Controller NameSpace Loader
$vendor->addPsr4(__NAMESPACE__ . '\\Source\\', __DIR__ .'/Source/');
$vendor->addPsr4(__NAMESPACE__ . '\\Controller\\', __DIR__ .'/Controller/');
$vendor->register();

/**
 * Add Dependencies
 * @return ClassLoader
 */
$app['loader'] = function () use ($vendor) : ClassLoader {
    return $vendor;
};

# Require Constant
$app->required(__DIR__ . '/Constant.php');

# Require Dependencies
$app->requires([
    __DIR__ . '/Container/Container.php',   # Container Dependencies
    __DIR__ . '/Middleware/Middleware.php', # Middleware
    __DIR__ . '/Route/Route.php',           # Routes
]);

# Require Runtime
$app->required(__DIR__ .'/RunTime.php');

return $app;
