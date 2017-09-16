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

namespace PentagonalProject\Prima\Web\Extension;

use PentagonalProject\Prima\App\Source\Extension;
use PentagonalProject\SlimService\Hook;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Example
 * @package PentagonalProject\Prima\Web\Extension
 */
class Example extends Extension
{
    /**
     * @var string extension name
     */
    protected $modular_name = 'Example Extension';

    /**
     * @var string module uri
     */
    protected $modular_uri = 'https://www.pentagonal.org';

    /**
     * @var string author name
     */
    protected $modular_author = 'Pentagonal';

    /**
     * @var string extension author url
     */
    protected $modular_author_uri = 'https://www.pentagonal.org';

    /**
     * @var string extension description
     */
    protected $modular_description = 'Module Description';

    /**
     * @var string extension version
     */
    protected $modular_version = '1.0';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // do init on first activated
        /**
         * @var Hook $hook
         */
        $hook = $this->getContainer()['hook'];
        $hook->add(HOOK_AFTER_ACTIVE_EXTENSIONS, [$this, 'hook'], 10, 5);
        // end of application
        $hook->add(HOOK_AFTER_RESPONSE_HOOK, function (ResponseInterface $response) {
            // do after end of response
        }, 10, 1);
    }

    /**
     * @param ContainerInterface $container
     * @param array $currentHookedExtensions
     * @param array $invalidExtensions
     * @param array $originalExtensionsFromDatabase
     * @param array|Extension[] $currentLoadedExtensions
     */
    public function hook(
        ContainerInterface $container,
        array $currentHookedExtensions,
        array $invalidExtensions,
        array $originalExtensionsFromDatabase,
        array $currentLoadedExtensions
    ) {
        // do process after all extensions loaded
    }
}
