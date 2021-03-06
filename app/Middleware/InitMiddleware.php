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

use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Hook;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Environment;
use Slim\Http\Uri;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/*! ------------------------------------------
 * Middleware init
 * -------------------------------------------
 */
$this->add(function (ServerRequestInterface $request, ResponseInterface $response, $next) {

    /*! ------------------------------------------
     * Fix Unwanted Index Rewrite
     * -------------------------------------------
     */
    /**
     * @var Environment $env
     */
    $env = $this['environment'];
    $requestScriptName = $env->get('SCRIPT_NAME');
    $requestUri = parse_url('http://example.com' . $env->get('REQUEST_URI'), PHP_URL_PATH);
    if (stripos($requestUri, $requestScriptName) === 0) {
        $env['SCRIPT_NAME'] = dirname($requestScriptName);
        $request = $request->withUri(Uri::createFromEnvironment($env));
    }

    /**
     * @var Hook[] $this
     */
    $this['hook']->call(HOOK_INIT_MIDDLEWARE, $this, $request, $response);

    return $next($request, $response);
});
