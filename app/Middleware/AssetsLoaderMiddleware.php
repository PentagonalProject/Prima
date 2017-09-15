<?php
namespace PentagonalProject\Prima\App\Middleware;

use PentagonalProject\Prima\App\Source\Extension;
use PentagonalProject\Prima\App\Source\ExtensionCollection;
use PentagonalProject\Prima\App\Source\Model\Option;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Exceptions\ModularNotFoundException;
use PentagonalProject\SlimService\Hook;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * Handler Asset to load
 */
$this->add(function (ServerRequestInterface $request, ResponseInterface $response, $next) {
    /**
     * @var Hook $hook
     * @var Option $option
     * @var ExtensionCollection $extensions
     */
    $hook = $this['hook'];
    $option = $this['option'];
    $extensions = $this['extension'];

    $activeExtensionsFromDB = $option->getOrUpdate('active.extensions', []);
    if (!is_array($activeExtensionsFromDB)) {
        $activeExtensionsFromDB = [];
        $option->update('active.extension', $activeExtensionsFromDB);
    } else {
        $activeExtensionFromDB2 = $activeExtensionsFromDB;
        $activeExtensionsFromDB = [];
        foreach ($activeExtensionFromDB2 as $ext) {
            if (!is_string($ext)) {
                continue;
            }
            // sanitize
            $ext =  strtolower(trim($ext));
            $activeExtensionsFromDB[$ext] = true;
        }
        $activeExtensionsFromDB = array_keys($activeExtensionsFromDB);
        if ($activeExtensionsFromDB !== $activeExtensionFromDB2) {
            $option->update('active.extensions', $activeExtensionsFromDB);
        }

        unset($activeExtensionFromDB2);
    }

    // call hook
    $hook->call('before.extensions.loaded', [$this], $activeExtensionsFromDB);

    $currentHookedExtensions = (array) $hook->apply('active.extension', $activeExtensionsFromDB, $this);

    /**
     * @var \Throwable[] $invalidHookedExtensions
     * @var string[]    $currentHookedExtensions
     * @var Extension[]    $currentLoadedExtensions
     */
    $invalidHookedExtensions = [];
    $currentLoadedExtensions = [];
    foreach ($currentHookedExtensions as $key => $extension) {
        if (!is_string($extension)) {
            // pass
            continue;
        }
        if (! $extensions->exist($extension)) {
            $invalidHookedExtensions[$extension] = new ModularNotFoundException(
                $extension,
                sprintf(
                    'Extension %s has not found!',
                    $extension
                )
            );
            continue;
        }
        try {
            // load extension
            $ext = $extensions->load($extension);
            $currentLoadedExtensions[$ext->getModularNameSelector()] = $ext;
        } catch (\Throwable $e) {
            $invalidHookedExtensions[$extension] = $e;
        }
    }

    $hook
        ->call(
            'after.extensions.loaded',
            [$this], // object container
            $currentHookedExtensions,   // active extension hook
            $invalidHookedExtensions,   // invalid extension from database
            $activeExtensionsFromDB,     // Original Extensions from database
            $currentLoadedExtensions    // extension loaded
        );

    return $next($request, $response);
});
