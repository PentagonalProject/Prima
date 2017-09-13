<?php
namespace PentagonalProject\Prima\App\Container;

use PentagonalProject\Prima\App\Source\ExtensionCollection;
use PentagonalProject\Prima\App\Source\ExtensionParser;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Hook;
use Psr\Container\ContainerInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * @param ContainerInterface $container
 *
 * @return ExtensionCollection
 */
$this['extension'] = function (ContainerInterface $container) : ExtensionCollection {
    $extensionsDir = dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'extensions';
    /**
     * @var Hook $hook
     */
    $hook = $container['hook'];
    $extensionsDir = $hook->apply('extensions.dir', $extensionsDir);
    $extension = new ExtensionCollection(
        $extensionsDir,
        new ExtensionParser($container)
    );

    $extension->scan();
    return $extension;
};
