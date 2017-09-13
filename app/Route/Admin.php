<?php
namespace PentagonalProject\Prima\App\Route;

use PentagonalProject\Prima\App\Source\Model\CurrentUser;
use PentagonalProject\Prima\App\Source\Theme;
use PentagonalProject\SlimService\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Http\Response;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

// grouping admin
$this->group('/manage', function () {
    /**
     * @var App $this
     */
    $this->any('[/]', function (ServerRequestInterface $request, ResponseInterface $response) {
        /**
         * @var CurrentUser[] $this
         * @var Response $response
         */
        if (!$this['user']->isLogin()) {
            return $response->withRedirect('/manage/login');
        }
        /**
         * @var Theme $theme
         */
        $theme = $this['theme.admin'];
        $theme['title'] = 'Dashboard';
        $theme->onceResponse('index', $response);
        return $response;
    })->setName('admin.dashboard');
    $this->any('/login[/]', function (ServerRequestInterface $request, ResponseInterface $response) {
            /**
             * @var CurrentUser[] $this
             * @var Response $response
             */
        if ($this['user']->isLogin()) {
            return $response->withRedirect('/manage');
        }
            /**
             * @var Theme $theme
             */
            $theme = $this['theme.admin'];
            $theme['title'] = 'Login To Member Area';
            $theme->onceResponse('index', $response);
            return $response;
    })->setName('admin.login');
});
