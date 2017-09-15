<?php
/**
 * Index Application
 */
namespace PentagonalProject\Prima;

use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Hook;
use Psr\Http\Message\ResponseInterface;

/**
 * @var Application $app
 */
$app = require __DIR__ .'/../app/App.php';
/**
 * @var ResponseInterface $response
 */
$response = $app->run(true);

# doing hook
if (isset($app['hook']) && $app['hook'] instanceof Hook) {
    $hook     = $app['hook'];
    $response = $hook->apply('response', $response, $app);
}

// output buffering
$app->respond($response);

/**
 * @return ResponseInterface
 */
return $response;
