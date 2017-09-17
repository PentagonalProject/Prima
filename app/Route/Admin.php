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

use PentagonalProject\Prima\App\Controller\Admin\AuthAccount;
use PentagonalProject\Prima\App\Controller\AdminBase;
use PentagonalProject\Prima\App\Source\Theme;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Hook;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Route;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/*! ------------------------------------------
 * Route for admin
 * -------------------------------------------
 */
// grouping admin
return $this
    # add grouping
    ->group(AdminBase::GROUP_PATTERN, function () {

        AdminBase::route($this, AdminBase::ANY, '[/]', 'index');
        // management
        AuthAccount::route($this, AdminBase::ANY, AdminBase::LOGIN_PATH . '[/]', 'login');
        AuthAccount::route($this, AdminBase::ANY, AdminBase::LOGOUT_PATH . '[/]', 'logout');
        AuthAccount::route($this, AdminBase::ANY, AdminBase::REGISTER_PATH . '[/]', 'register');
        AuthAccount::route($this, AdminBase::ANY, AdminBase::FORGOT_PATH . '[/]', 'forgot');
    })
    # add middleware after route match
    ->add(function (ServerRequestInterface $request, ResponseInterface $response, Route $next) {
        # unset Route if exists
        unset($this['route']);
        /**
         * Assert Route
         * @var Theme[]|Hook[] $this
         * @return Route
         */
        $this['route'] = function () use ($next) {
            return $next;
        };

        # hook
        $this['hook']->call(HOOK_GROUP_ROUTE_MIDDLEWARE, $this, $this['route'], 'admin');

        $this['theme.admin']->setRouteParams($next->getArguments());
        // include init if exists
        $this['theme.admin']->onceIgnore('init');
        return $next($request, $response);
    });
