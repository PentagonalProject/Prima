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

namespace PentagonalProject\Prima\App\Controller\Admin;

use PentagonalProject\Prima\App\Controller\AdminBase;
use PentagonalProject\SlimService\Hook;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\NotFoundException;
use Slim\Http\Response;

/**
 * Class AuthAccount
 * @package PentagonalProject\Prima\App\Controller\Admin
 */
class AuthAccount extends AdminBase
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface|static
     */
    public function logoutController(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        /**
         * If user has login do redirect to main admin area
         * @var Response $response
         */
        if (!$this->isLogin()) {
            return $this->redirectLogin($response);
        }

        $this->user->destroy();
        return $response->withRedirect($this->getLoginUri() . '?logout=true');
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface|static
     */
    public function loginController(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        /**
         * If user has login do redirect to main admin area
         * @var Response $response
         */
        if ($this->isLogin()) {
            return $response->withRedirect($this->getAdminUri(), 302);
        }

        $this->resetIs();
        $this->theme->isLoginPage = true;

        /**
         * @var Hook $hook
         */
        $hook = $this->container['hook'];
        $title = (string) $hook
            ->apply(
                HOOK_DEFAULT_TITLE_LOGIN,
                $this->getOrUpdateDefaultOption('login.title', 'Login To Member Area')
            );

        return $this->render(
            $request,
            $response,
            'login',
            $title
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function forgotController(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {

        /**
         * if user has logged do not found
         * @var Hook $hook
         */
        if ($this->isLogin()) {
            throw new NotFoundException(
                $request,
                $response
            );
        }

        $this->resetIs();
        $hook = $this->container['hook'];
        $allowForgot = $this->getOrUpdateDefaultOption('allow.forgot', 'yes');
        if (! $hook->apply(HOOK_ALLOW_FORGOT, trim(strtolower($allowForgot)) != 'no')) {
            throw new NotFoundException(
                $request,
                $response
            );
        }

        $this->theme->isForgotPage = true;

        $title = (string) $hook
            ->apply(
                HOOK_DEFAULT_TITLE_FORGOT,
                $this->getOrUpdateDefaultOption('forgot.title', 'Reset Your Password')
            );

        return $this->render($request, $response, 'forgot', $title);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function registerController(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {

        /**
         * if user has logged do not found
         * @var Hook $hook
         */
        if ($this->isLogin()) {
            throw new NotFoundException(
                $request,
                $response
            );
        }

        $this->resetIs();
        $hook = $this->container['hook'];
        $allowRegister = $this->getOrUpdateDefaultOption('allow.register', 'yes');
        if (! $hook->apply(HOOK_ALLOW_REGISTER, trim(strtolower($allowRegister)) != 'no')) {
            throw new NotFoundException(
                $request,
                $response
            );
        }

        $this->theme->isForgotPage = true;

        $title = (string) $hook
            ->apply(
                HOOK_DEFAULT_TITLE_FORGOT,
                $this->getOrUpdateDefaultOption('register.title', 'Register New Account')
            );

        return $this->render($request, $response, 'register', $title);
    }
}
