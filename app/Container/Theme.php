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

/*! ------------------------------------------
 * Theme Container
 * -------------------------------------------
 */

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
    $themesDir = $container['hook']->apply(HOOK_DIRECTORY_THEMES, $container, $themesDir);

    # hook
    $container['hook']->call(HOOK_BEFORE_LOAD_THEMES, $themesDir);

    $themeCollection = new ThemeCollection($themesDir, ThemeSource::class);
    $list = $themeCollection->getValidThemeList()->keys();

    if (!empty($list)) {
        $activeTheme        = reset($list);
        $currentActiveTheme = $activeTheme;
        $activeTheme = $container['hook']->apply(HOOK_ACTIVE_THEME, $currentActiveTheme, $themeCollection, $container);
        $themeCollection->setActiveTheme($activeTheme);
    }
    $container['log']->debug('Theme Collection Initiated');

    # hook
    $container['hook']->call(HOOK_AFTER_LOAD_THEMES, $container, $themeCollection);

    return $themeCollection;
};

/**
 * @param ContainerInterface $container
 *
 * @return Theme
 */
$this['theme'] = function (ContainerInterface $container) : Theme {
    /**
     * @var Hook[]|ThemeCollection[]|Logger[] $container
     */

    # hook
    $container['hook']->call(HOOK_BEFORE_LOAD_THEMES, $container, $container['themes']);

    $theme = $container['themes']->getActiveTheme();
    if (!$theme) {
        throw new RuntimeException(
            'There are no active admin theme'
        );
    }

    $theme->setContainer($container);
    // add before template loaded
    $theme->setBeforeLoadCallBack(function () use ($container) {
        $container['hook']->call(HOOK_BEFORE_LOAD_TEMPLATE, $container);
    });
    $container['log']->debug('Theme Initiated');

    # hook
    $container['hook']->call(HOOK_AFTER_LOAD_THEME, $container, $theme);

    return $theme;
};


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
    $themesDir = $container['hook']->apply(HOOK_DIRECTORY_THEMES_ADMIN, $themesDir);

    # hook
    $container['hook']->call(HOOK_BEFORE_LOAD_THEMES_ADMIN, $themesDir);

    $themeCollection = new ThemeAdminCollection($themesDir, ThemeSource::class);
    $list = $themeCollection->getValidThemeList()->keys();
    if (!empty($list)) {
        $activeTheme        = reset($list);
        $currentActiveTheme = $activeTheme;
        $activeTheme = $container['hook']->apply(
            HOOK_ACTIVE_THEME_ADMIN,
            $currentActiveTheme,
            $themeCollection,
            $container
        );

        $themeCollection->setActiveTheme($activeTheme);
    }
    $container['log']->debug('Theme Admin Collection Initiated');

    # hook
    $container['hook']->call(HOOK_AFTER_LOAD_THEMES_ADMIN, $container, $themeCollection);

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
     * @var ThemeCollection[]|Hook[]|Logger[] $container
     */
    # hook
    $container['hook']->call(HOOK_BEFORE_LOAD_THEMES, $container, $container['themes.admin']);

    $theme = $container['themes.admin']->getActiveTheme();

    if (!$theme) {
        throw new RuntimeException(
            'There are no active admin theme'
        );
    }

    $theme->setContainer($container);
    // add before template loaded
    $theme->setBeforeLoadCallBack(function () use ($container) {
        $container['hook']->call(HOOK_BEFORE_LOAD_THEMES_ADMIN, $container);
    });
    $container['log']->debug('Theme Admin Initiated');

    # hook
    $container['hook']->call(HOOK_AFTER_LOAD_THEME_ADMIN, $container, $theme);

    return $theme;
};
