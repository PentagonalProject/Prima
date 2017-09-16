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

namespace PentagonalProject\Prima\App\Controller;

use PentagonalProject\Prima\App\Source\Model\BaseController;
use PentagonalProject\SlimService\Hook;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PublicBase
 * @package PentagonalProject\Prima\App\Controller
 */
class PublicBase extends BaseController
{
    /*! ------------------------------------------
     * Base Constant Property
     * -------------------------------------------
     */
    const PREFIX_NAME       = 'public'; # prefix for route naming
    const THEME_CONTAINER   = 'theme';  # override theme for view

    /*! ------------------------------------------
     * Object Route Constant
     * -------------------------------------------
     */
    const GROUP_PATTERN     = '';       # pattern for group

    /**
     * @var string
     */
    protected $adminPath = AdminBase::GROUP_PATTERN;

    /**
     * Initial
     */
    protected function init()
    {
        $this->loginPath  = $this->adminPath . AdminBase::LOGIN_PATH;
        $this->logoutPath = $this->adminPath . AdminBase::LOGOUT_PATH;
        $this->resetIs();
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function indexController(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {

        /**
         * @var Hook $hook
         */
        $hook = $this->container['hook'];
        $title = (string) $hook
            ->apply(
                HOOK_DEFAULT_TITLE,
                $this->getOrUpdateDefaultOption('site.title', 'Welcome To Our Site')
            );

        return $this->render($request, $response, 'index', $title);
    }
}
