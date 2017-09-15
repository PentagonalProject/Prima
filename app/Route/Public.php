<?php
namespace PentagonalProject\Prima\App\Route;

use PentagonalProject\Prima\App\Controller\PublicBase;
use PentagonalProject\Prima\App\Source\Theme;
use PentagonalProject\SlimService\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Route;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

$this
    # add grouping
    ->group(PublicBase::GROUP_PATTERN, function () {
        // index
        PublicBase::route($this, PublicBase::ANY, '[/]', 'index');
    })
    # add middleware after route match
    ->add(function (ServerRequestInterface $request, ResponseInterface $response, Route $next) {
        # unset Route if exists
        unset($this['route']);
        /**
         * Assert Route
         * @var Theme[] $this
         * @return Route
         */
        $this['route'] = function () use ($next) {
            return $next;
        };
        $this['theme']->setRouteParams($next->getArguments());
        // include init if exists
        $this['theme']->onceIgnore('init');
        return $next($request, $response);
    });
