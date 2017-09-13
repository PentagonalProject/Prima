<?php
namespace PentagonalProject\Prima\App\Route;

use PentagonalProject\Prima\App\Source\Theme;
use PentagonalProject\SlimService\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

$this->group('', function () {
    /**
     * @var App $this
     */
    $this->any('/', function (ServerRequestInterface $request, ResponseInterface $response) {
        /**
         * @var Theme[] $this
         */
        return $this['theme']->onceResponse('index', $response);
    })->setName('public.index');
});
