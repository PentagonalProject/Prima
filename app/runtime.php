<?php
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

// hook for themes directory
$hook->add('themes.dir', function () {
    return dirname($_SERVER['SCRIPT_FILENAME'])
           . DIRECTORY_SEPARATOR
           . 'templates'
           . DIRECTORY_SEPARATOR
           . 'public';
});

// hook for themes directory
$hook->add('themes.admin.dir', function () {
    return dirname($_SERVER['SCRIPT_FILENAME'])
           . DIRECTORY_SEPARATOR
           . 'templates'
           . DIRECTORY_SEPARATOR
           . 'admin';
});

// hook for extensions directory
$hook->add('extensions.dir', function () {
    return dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'extensions';
});

// hook for active theme
$hook->add('active.theme', function ($activeTheme, $themeCollection, $container) {
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

// hook for active theme
$hook->add('active.admin.theme', function ($activeTheme, $themeCollection, $container) {
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

// hook response
$hook->add('response', function (ResponseInterface $response, $app) {
    if (isset($app['cookie']) && $app['cookie'] instanceof CookieSession) {
        /**
         * @var CookieSession $cookie
         */
        $cookie   = $app['cookie'];
        $response = $response->withAddedHeader('Set-Cookie', $cookie->toHeaders());
    }
    return $response;
}, 10, 2);

return $this;
