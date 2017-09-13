<?php
namespace PentagonalProject\Prima\App\Container;

use PentagonalProject\Prima\App\Source\Model\CurrentUser;
use PentagonalProject\Prima\App\Source\Model\Token\Auth;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Config;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Uri;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

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
     * @var Request $request
     * @var Uri $uri
     */
    $request = $container['request'];
    $uri = $request->getUri();
    $key  = $config->get('key');
    $key = $key && ! is_string($key) ? serialize($key) : (!is_string($key) ? sha1(__FILE__) : $key);
    $salt = $config->get('salt');
    $salt = $salt && ! is_string($salt) ? serialize($salt) : (!is_string($salt) ? sha1(__FILE__.$key) : $salt);
    $currentUser = new CurrentUser(
        $container['db'],
        $container['cookie'],
        md5($uri->getBaseUrl()),
        $key,
        $salt
    );

    $currentUser->init();
    return $currentUser;
};
