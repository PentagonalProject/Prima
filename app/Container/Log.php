<?php
namespace PentagonalProject\Prima\App\Container;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PentagonalProject\SlimService\Application;
use Psr\Container\ContainerInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/*! ---------------------------------------------------------------*
 * Debugger Container                                              |
 *                                                                 |
 * ----------------------------------------------------------------*
 */

/**
 * @param ContainerInterface $container
 *
 * @return Logger
 */
$this['log'] = function (ContainerInterface $container) : Logger {
    /**
     * @var Application[] $container
     */
    $appName = $container['app']->getName();
    $logger = new Logger($appName);
    $loggerName = $appName;
    $loggerName = preg_replace('/[^a-z0-9\-\.]/i', '_', $loggerName) ?: 'log';
    if ($loggerName[0] == '.') {
        $loggerName = '_'.$loggerName;
    }
    $log = $container->get('settings')['displayErrorDetails']
        ? '/dev/null'
        : __DIR__. '/../../Storage/Logs/' . $loggerName . '.log';
    $logger->pushHandler(
        new StreamHandler(
            $log,
            Logger::NOTICE
        )
    );

    return $logger;
};
