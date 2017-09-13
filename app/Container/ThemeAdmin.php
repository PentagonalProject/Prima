<?php
namespace PentagonalProject\Prima\App\Container;

use Monolog\Logger;
use PentagonalProject\Prima\App\Source\Theme as ThemeSource;
use PentagonalProject\Prima\App\Source\ThemeAdminCollection;
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
$this['themes.admin'] =  function (ContainerInterface $container) : ThemeCollection {
    /**
     * @var Logger[]|Hook[] $container
     */
    $themesDir = dirname($_SERVER['SCRIPT_FILENAME'])
                 . DIRECTORY_SEPARATOR
                 . 'templates'
                 . DIRECTORY_SEPARATOR
                 . 'admin';
    $themesDir = $container['hook']->apply('themes.admin.dir', $themesDir);
    $themeCollection = new ThemeAdminCollection($themesDir, ThemeSource::class);
    $container['log']->debug('Theme Admin Collection Initiated');
    $list = $themeCollection->getValidThemeList()->keys();
    if (!empty($list)) {
        $activeTheme        = reset($list);
        $currentActiveTheme = $activeTheme;
        $activeTheme = $container['hook']->apply(
            'active.admin.theme',
            $currentActiveTheme,
            $themeCollection,
            $container
        );
        $themeCollection->setActiveTheme($activeTheme);
    }
    return $themeCollection;
};

/**
 * @param ContainerInterface $container
 *
 * @return Theme
 * @throw RuntimeException
 */
$this['theme.admin'] = function (ContainerInterface $container) : Theme {
    /**
     * @var ThemeCollection $themes
     */
    $themes = $container['themes.admin'];
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
        $hook->apply('before.admin.template_load', $container);
    });

    return $theme;
};
