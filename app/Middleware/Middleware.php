<?php
namespace PentagonalProject\Prima\App\Middleware;

use PentagonalProject\SlimService\Application;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

# Required Middleware
$this->required(__DIR__ . '/ParsedBodyMiddleware.php');
$this->required(__DIR__ . '/ErrorHandlerMiddleware.php');
