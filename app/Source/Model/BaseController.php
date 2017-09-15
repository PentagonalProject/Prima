<?php
namespace PentagonalProject\Prima\App\Source\Model;

use PentagonalProject\Prima\App\Source\Theme;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Interfaces\RouteInterface;

/**
 * Class BaseController
 * @package PentagonalProject\Prima\App\Source
 */
abstract class BaseController
{
    const PREFIX_NAME   = '';
    const THEME_CONTAINER = 'theme';

    const ANY = 'any';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Theme
     */
    protected $theme;

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
        $current = $this->container['user'];
        return $current->isLogin();
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

        $name = strtolower($controllerMethod);
        return $route->setName(static::PREFIX_NAME .  ".{$name}");
    }

    /**
     * @param string $method
     *
     * @return string
     */
    public static function callback(string $method) : string
    {
        return get_called_class() . ":{$method}Controller";
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
