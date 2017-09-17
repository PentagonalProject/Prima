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

use PentagonalProject\Prima\App\Source\CookieSession;
use PentagonalProject\Prima\App\Source\Model\Option;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Hook;
use PentagonalProject\SlimService\ThemeCollection;
use Psr\Http\Message\ResponseInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * @var Hook $hook
 */
$hook = $this['hook'];

/*! ------------------------------------------
 * Hook for Themes Directory
 * -------------------------------------------
 */
$hook->add(HOOK_DIRECTORY_THEMES, function () {
    return dirname($_SERVER['SCRIPT_FILENAME'])
           . DIRECTORY_SEPARATOR
           . 'templates'
           . DIRECTORY_SEPARATOR
           . 'public';
});


/*! ------------------------------------------
 * Hoo for Themes Admin Directory
 * -------------------------------------------
 */
$hook->add(HOOK_DIRECTORY_THEMES_ADMIN, function () {
    return dirname($_SERVER['SCRIPT_FILENAME'])
           . DIRECTORY_SEPARATOR
           . 'templates'
           . DIRECTORY_SEPARATOR
           . 'admin';
});


/*! ------------------------------------------
 * Hoo for Extensions Directory
 * -------------------------------------------
 */
$hook->add(HOOK_DIRECTORY_EXTENSIONS, function () {
    return dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'extensions';
});


/*! ------------------------------------------
 * Hoo for Active Theme
 * -------------------------------------------
 */
$hook->add(HOOK_ACTIVE_THEME, function ($activeTheme, $themeCollection, $container) {
    /**
     * @var Option $options
     * @var ThemeCollection $themeCollection
     */
    $options = $container['option'];
    $currentActiveTheme = $options->getOrUpdate('active.theme', $activeTheme, true);
    if (! is_string($currentActiveTheme) || ! $themeCollection->isThemeIsValid($currentActiveTheme)) {
        $currentActiveTheme = $activeTheme;
        $options->update('active.theme', $currentActiveTheme);
    }

    return $activeTheme;
}, 10, 3);


/*! ------------------------------------------
 * Hoo for Active Admin Theme
 * -------------------------------------------
 */
$hook->add(HOOK_ACTIVE_THEME_ADMIN, function ($activeTheme, $themeCollection, $container) {
    /**
     * @var Option $options
     * @var ThemeCollection $themeCollection
     */
    $options = $container['option'];
    $currentActiveTheme = $options->getOrUpdate('active.admin.theme', $activeTheme, true);
    if (! is_string($currentActiveTheme) || ! $themeCollection->isThemeIsValid($currentActiveTheme)) {
        $currentActiveTheme = $activeTheme;
        $options->update('active.admin.theme', $currentActiveTheme);
    }

    return $activeTheme;
}, 10, 3);


/*! ------------------------------------------
 * Hoo for Response for add the end of
 * priority and set to 100
 * -------------------------------------------
 */
$hook->add(HOOK_RESPONSE, function (ResponseInterface $response, $app) {
    if (isset($app['cookie']) && $app['cookie'] instanceof CookieSession) {
        /**
         * @var CookieSession $cookie
         */
        $cookie   = $app['cookie'];
        $response = $response->withAddedHeader('Set-Cookie', $cookie->toHeaders());
    }
    /**
     * Fix Header Length for added write body
     */
    if ($response->hasHeader('Content-Length')) {
        $bodySize = $response->getBody()->getSize();
        $headerLength = $response->getHeader('Content-Length')[0];
        if ($bodySize <> $headerLength) {
            $response = $response->withHeader('Content-Length', (string) $bodySize);
        }
    }

    return $response;
}, 100, 2);

return $this;
