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

/**
 * Index Application
 */

declare(strict_types=1);

namespace PentagonalProject\Prima;

use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Hook;
use Psr\Http\Message\ResponseInterface;

/**
 * @var Application|Hook[] $app
 * @var ResponseInterface $response
 */
$app = require __DIR__ .'/../app/App.php';
$response = $app->run(true);

/*! ------------------------------------------
 * Doing hook call
 * -------------------------------------------
 */
# hook before hooking response
$app['hook']->call(HOOK_BEFORE_RESPONSE_HOOK, $response, $app);
# hook value for response
$response = $app['hook']->apply(HOOK_RESPONSE, $response, $app);
# hook after hooking response
$app['hook']->call(HOOK_AFTER_RESPONSE_HOOK, $response, $app);
# output buffering
$app->respond($response);
# call hook after buffer
$app['hook']->call(HOOK_AFTER_RESPONSE_BUFFER, $response, $app);

return $response;
