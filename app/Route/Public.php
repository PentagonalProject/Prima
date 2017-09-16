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

namespace PentagonalProject\Prima\App\Route;

use PentagonalProject\Prima\App\Controller\PublicBase;
use PentagonalProject\Prima\App\Source\Theme;
use PentagonalProject\SlimService\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Route;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/*! ------------------------------------------
 * Route for public / common
 * -------------------------------------------
 */
$this
    # add grouping
    ->group(PublicBase::GROUP_PATTERN, function () {

        PublicBase::route($this, PublicBase::ANY, '[/]', 'index');
    })
    # add middleware after route match
    ->add(function (ServerRequestInterface $request, ResponseInterface $response, Route $next) {
        # unset Route if exists
        unset($this['route']);
        /**
         * Assert Route
         * @var Theme[] $this
         * @return Route
         */
        $this['route'] = function () use ($next) {
            return $next;
        };
        $this['theme']->setRouteParams($next->getArguments());
        // include init if exists
        $this['theme']->onceIgnore('init');
        return $next($request, $response);
    });
