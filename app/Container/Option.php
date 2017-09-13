<?php
namespace PentagonalProject\Prima\App\Container;

use PentagonalProject\Prima\App\Source\Model\Option;
use PentagonalProject\SlimService\Application;
use Psr\Container\ContainerInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * @param ContainerInterface $container
 *
 * @return Option
 */
$this['option'] = function (ContainerInterface $container) : Option {
    return new Option($container['db']);
};
