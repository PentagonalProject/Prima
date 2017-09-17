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

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/*! ------------------------------------------
 * Require Middleware
 * -------------------------------------------
 */
$this->required(__DIR__ . '/InitMiddleware.php');           # Init on first
$this->required(__DIR__ . '/ParsedBodyMiddleware.php');     # Parse for body
$this->required(__DIR__ . '/ErrorHandlerMiddleware.php');   # Handler Error
$this->required(__DIR__ . '/AssetsLoaderMiddleware.php');   # Load assets
$this->required(__DIR__ . '/LastMiddleware.php');           # Last middleware
