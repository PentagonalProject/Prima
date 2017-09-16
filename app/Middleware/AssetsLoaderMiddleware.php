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

/*! ------------------------------------------
 * Middleware to handle asset
 * -------------------------------------------
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
        $option->update('active.extensions', $activeExtensionsFromDB);
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
    $hook->call(HOOK_BEFORE_ACTIVE_EXTENSIONS, $this, $activeExtensionsFromDB);

    $currentHookedExtensions = (array) $hook->apply(HOOK_ACTIVE_EXTENSIONS, $activeExtensionsFromDB, $this);

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
            HOOK_AFTER_ACTIVE_EXTENSIONS,
            $this, // object container
            $currentHookedExtensions,   // active extension hook
            $invalidHookedExtensions,   // invalid extension from database
            $activeExtensionsFromDB,     // Original Extensions from database
            $currentLoadedExtensions    // extension loaded
        );

    return $next($request, $response);
});
