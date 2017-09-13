<?php
namespace PentagonalProject\Prima\App\Container;

use Monolog\Logger;
use PentagonalProject\Prima\App\Source\Theme as ThemeSource;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Hook;
use PentagonalProject\SlimService\Theme;
use PentagonalProject\SlimService\ThemeCollection;
use Psr\Container\ContainerInterface;
use RuntimeException;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * @param ContainerInterface $container
 *
 * @return ThemeCollection
 */
$this['themes'] =  function (ContainerInterface $container) : ThemeCollection {
    /**
     * @var Logger[]|Hook[] $container
     */
    $themesDir = dirname($_SERVER['SCRIPT_FILENAME'])
                 . DIRECTORY_SEPARATOR
                 . 'templates'
                 . DIRECTORY_SEPARATOR
                 . 'public';
    $themesDir = $container['hook']->apply('themes.dir', $themesDir);
    $themeCollection = new ThemeCollection($themesDir, ThemeSource::class);
    $container['log']->debug('Theme Collection Initiated');
    $list = $themeCollection->getValidThemeList()->keys();
    if (!empty($list)) {
        $activeTheme        = reset($list);
        $currentActiveTheme = $activeTheme;
        $activeTheme = $container['hook']->apply('active.theme', $currentActiveTheme, $themeCollection, $container);
        $themeCollection->setActiveTheme($activeTheme);
    }

    return $themeCollection;
};

/**
 * @param ContainerInterface $container
 *
 * @return Theme
 */
$this['theme'] = function (ContainerInterface $container) : Theme {
    /**
     * @var ThemeCollection $themes
     */
    $themes = $container['themes'];
    $theme = $themes->getActiveTheme();
    if (!$theme) {
        throw new RuntimeException(
            'There are no active admin theme'
        );
    }
    $theme->setContainer($container);
    // add before template loaded
    $theme->setBeforeLoadCallBack(function () use ($container) {
        /**
         * @var Hook $hook
         */
        $hook = $container['hook'];
        $hook->apply('before.template_load', $container);
    });

    return $theme;
};
