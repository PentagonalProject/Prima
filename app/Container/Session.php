<?php
namespace PentagonalProject\Prima\App\Container;

use Monolog\Logger;
use PentagonalProject\Prima\App\Source\Flash;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Config;
use PentagonalProject\SlimService\Session;
use Psr\Container\ContainerInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * @param ContainerInterface $container
 *
 * @return Session
 */
$this['session'] = function (ContainerInterface $container) : Session {
    $session = new Session();
    /** @var Config $config */
    $config = $container['config'];
    $configSession = (array) $config->get('session');
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
        $cookieParamsKey = ['lifetime', 'path', 'domain', 'secure', 'httponly'];
        $cookieParams = [];
        foreach ($configSession as $key => $value) {
            in_array($key, $cookieParamsKey) && $cookieParams[$key] = $value;
        }

        $session->getSession()->setCookieParams($cookieParams);
    }

    $session->startOrResume();
    /** @var Logger[] $container */
    $container['log']->debug('Session initiated');
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
