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

    $activeExtensionFromDB = $option->getOrUpdate('active.extensions', []);
    if (!is_array($activeExtensionFromDB)) {
        $activeExtensionFromDB = [];
        $option->update('active.extension', $activeExtensionFromDB);
    } else {
        $activeExtensionFromDB2 = $activeExtensionFromDB;
        $activeExtensionFromDB = [];
        foreach ($activeExtensionFromDB2 as $ext) {
            if (!is_string($ext)) {
                continue;
            }
            // sanitize
            $ext =  strtolower(trim($ext));
            $activeExtensionFromDB[$ext] = true;
        }
        $activeExtensionFromDB = array_keys($activeExtensionFromDB);
        if ($activeExtensionFromDB !== $activeExtensionFromDB2) {
            $option->update('active.extensions', $activeExtensionFromDB);
        }

        unset($activeExtensionFromDB2);
    }

    // call hook
    $hook->call('before.extensions.loaded', [$this], $activeExtensionFromDB);

    $currentHookedExtensions = (array) $hook->apply('active.extension', $activeExtensionFromDB, $this);

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
            $activeExtensionFromDB,     // Original Extensions from database
            $currentLoadedExtensions    // extension loaded
        );

    return $next($request, $response);
});
