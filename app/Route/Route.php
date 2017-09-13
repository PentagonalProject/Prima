<?php
namespace PentagonalProject\Prima\App\Route;

use PentagonalProject\SlimService\Application;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

$this->required(__DIR__ . '/Admin.php');
$this->required(__DIR__ . '/Public.php');
