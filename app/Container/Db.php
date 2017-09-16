<?php
/**
 * MIT License
 *
 * Copyright (c) 2017, Pentagonal
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace PentagonalProject\Prima\App\Container;

use Monolog\Logger;
use PentagonalProject\SlimService\Application;
use PentagonalProject\SlimService\Config;
use PentagonalProject\SlimService\Database;
use PentagonalProject\SlimService\Hook;
use Psr\Container\ContainerInterface;

if (!isset($this) || ! $this instanceof Application) {
    return;
}

/*! ------------------------------------------
 * Database Container
 * -------------------------------------------
 */

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
     * @var Logger[]|Hook[] $container
     */
    $config = $config->toArray();

    # hook
    $container['hook']->call(HOOK_BEFORE_LOAD_DB, $container, $config);

    $database = new Database($config);
    $database->setFetchMode(\PDO::FETCH_ASSOC);
    $connection = $database->getWrappedConnection();
    $connection->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
    $container['log']->debug('Database Initiated');

    # hook
    $container['hook']->call(HOOK_AFTER_LOAD_DB, $container, $database);

    return $database;
};
