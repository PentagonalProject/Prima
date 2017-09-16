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

use Monolog\Logger;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\ErrorHandler;
use PentagonalProject\SlimService\Theme;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/*! ------------------------------------------
 * Middleware to handle Output Display
 * -------------------------------------------
 */
$this->add(function (ServerRequestInterface $request, ResponseInterface $response, $next) {
    $callBack = function (ServerRequestInterface $request, ResponseInterface $response, $ex = null) {
        /**
         * @var Theme[]|ContainerInterface|ResponseInterface[] $this
         */
        $statusCode = $this['response']->getStatusCode();
        $response = $response->withStatus($statusCode);
        if ($statusCode === 500 && $ex instanceof \Throwable) {
            /**
             * Handle Template 500 Error if there was unhandled error on 500.phtml
             */
            set_exception_handler(function (\Throwable $e) {
                // clearing buffer if possible
                if (($level = ob_get_level()) && ob_get_length() > 0) {
                    while ($level > 0 && ob_get_length() > 0) {
                        $level--;
                        ob_end_clean();
                    }
                }

                /**
                 * @var Logger[]|App[]|ServerRequestInterface[]|ResponseInterface[]|ContainerInterface $this
                 */
                $error = new ErrorHandler($this->get('settings')['displayErrorDetails']);
                $error->setLogger($this['log']);
                $response = $error($this['request'], $this['response'], $e);
                $this['slim']->respond($response);
                restore_exception_handler();
                exit(255);
            });
        }

        $theme = $this['theme'];
        $theme->isSearch = false;
        ob_start();
        $theme->load((string) $statusCode);
        $content = ob_get_clean();
        $body = $response->getBody();
        $body->write($content);
        return $response->withBody($body);
    };

    $this['notFoundHandler'] = function (ContainerInterface $container) use ($callBack) {
        /**
         * @var ResponseInterface $response
         * Set Override 404
         */
        $response = $container['response'];
        unset($this['response']);
        $container['response'] = $response->withStatus(404);
        return $callBack;
    };

    $this['notAllowedHandler'] = function (ContainerInterface $container) use ($callBack) {
        /**
         * @var ResponseInterface $response
         * Set Override 405
         */
        $response = $container['response'];
        unset($container['response']);
        $container['response'] = $response->withStatus(405);
        return $callBack;
    };

    if (empty($this['settings']['displayErrorDetails'])) {
        unset($this['errorHandler'], $this['phpErrorHandler']);
        $handler = function (ContainerInterface $container) use ($callBack) {
            /**
             * @var ResponseInterface $response
             * Set Override 500
             */
            $response = $container['response'];
            unset($container['response']);
            $container['response'] = $response->withStatus(500);
            return $callBack;
        };

        $this['errorHandler'] = $handler;
        $this['phpErrorHandler'] = $handler;
    }

    return $next($request, $response);
});
