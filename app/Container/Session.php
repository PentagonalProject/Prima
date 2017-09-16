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

use Monolog\Logger;
use PentagonalProject\Prima\App\Source\Flash;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Config;
use PentagonalProject\SlimService\Hook;
use PentagonalProject\SlimService\Session;
use Psr\Container\ContainerInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/*! ------------------------------------------
 * Session Container
 * -------------------------------------------
 */

/**
 * @param ContainerInterface $container
 *
 * @return Session
 */
$this['session'] = function (ContainerInterface $container) : Session {
    /**
     * @var Logger[]|Hook[]|Config[] $container
     */
    $configSession = (array) $container['config']->get('session');

    $cookieParamsKey = ['lifetime', 'path', 'domain', 'secure', 'httponly'];
    $cookieParams = [];
    foreach ($configSession as $key => $value) {
        in_array($key, $cookieParamsKey) && $cookieParams[$key] = $value;
    }

    # hook
    $container['hook']->call(HOOK_AFTER_LOAD_SESSION, $container, $configSession, $cookieParams);

    $session = new Session();
    if (!empty($configSession)) {
        if (isset($configSession['save_path']) && $configSession['save_path']) {
            $session->getSession()->setSavePath($configSession['save_path']);
        }
        if (isset($configSession['name']) && $configSession['name']) {
            $session->getSession()->setName($configSession['name']);
        }
        if (isset($configSession['cache_limiter']) && $configSession['cache_limiter']) {
            $session->getSession()->setCacheLimiter($configSession['cache_limiter']);
        }
        if (isset($configSession['segment_name']) && $configSession['segment_name']) {
            $session->setSegmentName($configSession['segment_name']);
        }
    }

    if (!empty($cookieParams)) {
        $session->getSession()->setCookieParams($cookieParams);
    }

    $session->startOrResume();
    $container['log']->debug('Session initiated started');

    # hook
    $container['hook']->call(HOOK_AFTER_LOAD_SESSION, $container, $session);

    return $session;
};

/**
 * @param ContainerInterface $container
 *
 * @return Flash
 */
$this['flash'] = function (ContainerInterface $container) : Flash {
    return new Flash($container['session']);
};
