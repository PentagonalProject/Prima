<?php
namespace PentagonalProject\Prima\App\Container;

use Monolog\Logger;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Config;
use PentagonalProject\SlimService\Database;
use Psr\Container\ContainerInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/**
 * @param ContainerInterface $container
 *
 * @return Database
 */
$this['db'] = function (ContainerInterface $container) : Database {
    /**
     * @var Config $config
     */
    $config = $container['config'];
    $config = $config->get('db', null);
    if (!$config instanceof Config) {
        throw new \RuntimeException(
            'Database configuration invalid.',
            E_WARNING
        );
    }

    /**
     * @var array $config
     * @var \PDO $connection
     * @var Logger[] $container
     */
    $config = $config->toArray();
    $database = new Database($config);
    $database->setFetchMode(\PDO::FETCH_ASSOC);
    $connection = $database->getWrappedConnection();
    $connection->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
    $container['log']->debug('Database Initiated');
    return $database;
};
