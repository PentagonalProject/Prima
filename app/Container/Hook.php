<?php
namespace PentagonalProject\Prima\App\Container;

use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Hook;
use PentagonalProject\SlimService\PropertyHookAble;
use Psr\Container\ContainerInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * @return Hook
 */
$this['hook'] = function () : Hook {
    return new Hook();
};


/**
 * @param ContainerInterface $container
 *
 * @return PropertyHookAble
 */
$this['hook.property'] = function (ContainerInterface $container) : PropertyHookAble {
    return new PropertyHookAble($container['hook']);
};
