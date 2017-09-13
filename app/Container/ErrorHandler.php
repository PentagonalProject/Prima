<?php
namespace PentagonalProject\Prima\App\Container;

use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\ErrorHandler;
use Psr\Container\ContainerInterface;
use Slim\Handlers\AbstractHandler;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * @param ContainerInterface $container
 *
 * @return AbstractHandler
 */
$this['errorHandler'] = function (ContainerInterface $container) : AbstractHandler {
    $error = new ErrorHandler($container->get('settings')['displayErrorDetails']);
    $error->setLogger($container['log']);
    return $error;
};

/**
 * @param ContainerInterface $container
 *
 * @return AbstractHandler
 */
$this['phpErrorHandler'] = function (ContainerInterface $container) : AbstractHandler {
    $error = new ErrorHandler($container->get('settings')['displayErrorDetails']);
    $error->setLogger($container['log']);
    return $error;
};
