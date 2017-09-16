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

namespace PentagonalProject\Prima\App\Container;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PentagonalProject\SlimService\Application;
use Psr\Container\ContainerInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/*! ------------------------------------------
 * Debugging Container
 * -------------------------------------------
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
