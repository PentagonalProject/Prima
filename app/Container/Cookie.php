<?php
namespace PentagonalProject\Prima\App\Container;

use PentagonalProject\Prima\App\Source\CookieSession;
use PentagonalProject\SlimService\Application;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Cookies;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * @param ContainerInterface $container
 *
 * @return CookieSession
 */
$this['cookie'] = function (ContainerInterface $container) : CookieSession {
    /**
     * @var ServerRequestInterface[] $container
     */
    $cookies = new Cookies($container['request']->getCookieParams());
    $cookieSession = new CookieSession($cookies);
    return $cookieSession;
};
