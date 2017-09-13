<?php
namespace PentagonalProject\Prima\App\Container;

use PentagonalProject\SlimService\Application;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

# Require Container
$this->required(__DIR__ . '/Cookie.php');
$this->required(__DIR__ . '/Db.php');
$this->required(__DIR__ . '/Db.php');
$this->required(__DIR__ . '/Environment.php');
$this->required(__DIR__ . '/ErrorHandler.php');
$this->required(__DIR__ . '/Extension.php');
$this->required(__DIR__ . '/Hook.php');
$this->required(__DIR__ . '/Log.php');
$this->required(__DIR__ . '/Option.php');
$this->required(__DIR__ . '/Session.php');
$this->required(__DIR__ . '/Theme.php');
$this->required(__DIR__ . '/ThemeAdmin.php');
$this->required(__DIR__ . '/User.php');
