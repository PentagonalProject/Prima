<?php
namespace PentagonalProject\Prima\App\Middleware;

use PentagonalProject\Prima\App\Source\CookieSession;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Configurator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Cookies;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * Middleware Body Parser & Cookie for Container
 */
$this->add(function (ServerRequestInterface $request, ResponseInterface $response, $next) {
    unset($this['input.post'], $this['input.get']);

    $this['input.body'] = function () use ($request) : Configurator {
        return new Configurator((array) $request->getParsedBody());
    };

    $this['input.query']  =  function () use ($request) : Configurator {
        return new Configurator((array)$request->getQueryParams());
    };

    return $next($request, $response);
});
