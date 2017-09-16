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
use PentagonalProject\Prima\App\Source\CookieSession;
use PentagonalProject\Prima\App\Source\Model\CurrentUser;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Config;
use PentagonalProject\SlimService\Database;
use PentagonalProject\SlimService\Hook;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Uri;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/*! ------------------------------------------
 * Model User Container
 * -------------------------------------------
 */

/**
 * @param ContainerInterface $container
 *
 * @return CurrentUser
 */
$this['user'] = function (ContainerInterface $container) : CurrentUser {
    $config = $container['config']['security'];
    if (! $config instanceof Config) {
        $config = new Config();
    }

    /**
     * @var Request[]|Hook[]|Database[]|CookieSession[]|Logger[] $container
     * @var Uri $uri
     */
    $uri = $container['request']->getUri();
    $key  = $config->get('key');
    $key = $key && ! is_string($key) ? serialize($key) : (!is_string($key) ? sha1(__FILE__) : $key);
    $salt = $config->get('salt');
    $salt = $salt && ! is_string($salt) ? serialize($salt) : (!is_string($salt) ? sha1(__FILE__.$key) : $salt);

    # hook
    $container['hook']->call(HOOK_BEFORE_LOAD_CURRENT_USER, $container, $key, $salt, $uri);

    $currentUser = new CurrentUser(
        $container['db'],
        $container['cookie'],
        md5($uri->getBaseUrl()),
        $key,
        $salt
    );

    $prefix = (string) $container['hook']
        ->apply(PREFIX_SESSION_COOKIE, $currentUser->getSessionPrefix(), $currentUser);
    $currentUser->setSessionPrefix($prefix);
    $currentUser->init();
    $container['log']->debug('Current User Initiated');

    # hook
    $container['hook']->call(HOOK_AFTER_LOAD_CURRENT_USER, $container, $currentUser);

    return $currentUser;
};
