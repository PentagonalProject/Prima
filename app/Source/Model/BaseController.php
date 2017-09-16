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

namespace PentagonalProject\Prima\App\Source\Model;

use PentagonalProject\Prima\App\Source\Theme;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;
use Slim\Interfaces\RouteInterface;

/**
 * Class BaseController
 * @package PentagonalProject\Prima\App\Source
 */
abstract class BaseController
{
    const SUFFIX_CONTROLLER = 'Controller';
    const PREFIX_NAME       = '';
    const THEME_CONTAINER   = 'theme';
    const ANY = 'any';

    /**
     * @var string
     */
    protected $adminPath = '/manage';

    /**
     * @var string
     */
    protected $loginPath = '/manage/login';

    /**
     * @var string
     */
    protected $logoutPath = '/manage/logout';

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Theme
     */
    protected $theme;

    /**
     * @var CurrentUser
     */
    protected $user;

    /**
     * BaseController constructor.
     *
     * @param ContainerInterface $container
     */
    final public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->theme = $this->container[static::THEME_CONTAINER];
        $this->init();
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getBaseUrl($path = '') : string
    {
        if (!isset($this->baseUri)) {
            /**
             * @var Request $request
             * @var Uri $uri
             */
            $request = $this->container['request'];
            $uri     = $request->getUri();
            $this->baseUri = rtrim($uri->getBaseUrl(), '/') . '/';
        }
        if (!is_null($path) && ! is_bool($path) && ! is_string($path) && !is_numeric($path)) {
            $path = is_resource($path) ? '' : gettype($path);
        }

        $path = (string) $path;
        return $this->baseUri . ltrim($path, '/');
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getBaseUri($path = ''): string
    {
        return $this->getBaseUrl($path);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return string
     */
    public function getAdminPath(): string
    {
        return $this->adminPath;
    }

    /**
     * @return string
     */
    public function getLoginPath() : string
    {
        return $this->loginPath;
    }

    /**
     * @return string
     */
    public function getLogoutPath() : string
    {
        return $this->logoutPath;
    }

    /**
     * @return string
     */
    public function getAdminUri() : string
    {
        return $this->getBaseUrl($this->getAdminPath());
    }

    /**
     * @return string
     */
    public function getLoginUri() : string
    {
        return $this->getBaseUrl($this->getLoginPath());
    }

    /**
     * @return string
     */
    public function getLogoutUri() : string
    {
        return $this->getBaseUrl($this->getLogoutPath());
    }

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    protected function redirectLogin(ResponseInterface $response) : ResponseInterface
    {
        /**
         * @var Response $response
         */
        return $response->withRedirect($this->getLoginUri());
    }

    /**
     * @param string $name
     * @param string $default
     * @param bool   $autoload
     *
     * @return string
     */
    protected function getOrUpdateDefaultOption(string $name, string $default, $autoload = true) : string
    {
        /**
         * @var Option $option
         */
        $option = $this->container['option'];
        $title = $option->getOrUpdate($name, $default, $autoload);
        if (!is_string($title)) {
            $title = $default;
            $option->update($name, $title, $autoload);
        }

        return $title;
    }

    /**
     * Reset
     */
    protected function resetIs()
    {
        $this->theme->isSearch       = false;
        $this->theme->isAdminArea    = false;
        $this->theme->isLoginPage    = false;
        $this->theme->isForgotPage   = false;
        $this->theme->isRegisterPage = false;
    }

    /**
     * @return mixed
     */
    abstract protected function init();

    /**
     * @return bool
     */
    public function isLogin() : bool
    {
        /**
         * @var CurrentUser $current
         */
        $this->user = $this->container['user'];
        return $this->user->isLogin();
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $file
     * @param null $title
     *
     * @return ResponseInterface
     */
    protected function render(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $file,
        $title = null
    ) : ResponseInterface {
        if (!$this->theme instanceof Theme) {
            throw new \RuntimeException(
                'Theme has not declared.',
                E_WARNING
            );
        }

        $this->theme[Request::class] = $request;
        is_string($title) && $this->theme['title'] = $title;
        return $this->theme->onceResponse($file, $response);
    }

    /**
     * @param App $app
     * @param string $method
     * @param string $pattern
     * @param string $controllerMethod
     *
     * @return RouteInterface
     */
    public static function route(
        App $app,
        $method,
        string $pattern,
        string $controllerMethod
    ) : RouteInterface {
        $callback = self::callback($controllerMethod);
        if (is_string($method) && strtolower($method) == 'any') {
            $route = $app->any($pattern, $callback);
        } else {
            $method = !is_array($method) ? [$method] : $method;
            $route = $app->map($method, $pattern, $callback);
        }

        return $route->setName(static::PREFIX_NAME . "." . $controllerMethod);
    }

    /**
     * The Controller Method has Suffix Controller
     * @param string $method
     *
     * @return string
     */
    public static function callback(string $method) : string
    {
        return get_called_class() . ":" . $method . static::SUFFIX_CONTROLLER;
    }

    /**
     * @param string $name
     * @param string $arguments
     *
     * @throws NotFoundException
     */
    public function __call($name, $arguments)
    {
        throw new \RuntimeException(
            sprintf('Controller callback for %s has not exists', self::callback($name)),
            E_WARNING
        );
    }
}
