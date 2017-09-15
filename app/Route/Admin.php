<?php
namespace PentagonalProject\Prima\App\Route;

use PentagonalProject\Prima\App\Controller\AdminBase;
use PentagonalProject\Prima\App\Source\Theme;
use PentagonalProject\SlimService\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Route;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

// grouping admin
return $this
    # add grouping
    ->group(AdminBase::GROUP_PATTERN, function () {
        /**
         * @var App $this
         */
        AdminBase::route($this, AdminBase::ANY, '[/]', 'index');
        AdminBase::route($this, AdminBase::ANY, AdminBase::LOGIN_PATH. '[/]', 'login');
        AdminBase::route($this, AdminBase::ANY, AdminBase::LOGOUT_PATH. '[/]', 'logout');
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
        $this['theme.admin']->setRouteParams($next->getArguments());
        // include init if exists
        $this['theme.admin']->onceIgnore('init');
        return $next($request, $response);
    });
