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

namespace PentagonalProject\Prima\App\Source\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use PentagonalProject\SlimService\Database;
use PentagonalProject\SlimService\Sanitizer;
use Slim\Collection;

/**
 * Class Option
 * @package PentagonalProject\Prima\App\Source
 */
class Option implements \ArrayAccess
{
    const TABLE_NAME = 'options';
    const OPTION_ID = 'option_id';
    const OPTION_NAME = 'option_name';
    const OPTION_VALUE = 'option_value';
    const OPTION_AUTOLOAD = 'option_autoload';

    /**
     * @var Collection
     */
    protected $options;

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var Database
     */
    protected $db;

    /**
     * Option constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->options = new Collection();
        $this->db = $database;
        $this->table = $this->db->prefixTables(self::TABLE_NAME);
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() : QueryBuilder
    {
        return $this->db->createQueryBuilder();
    }

    /**
     * Database Init
     */
    protected function init()
    {
        $stmt = $this
            ->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('LOWER('.self::OPTION_AUTOLOAD.')=:autoload')
            ->setParameter(':autoload', 'yes')
            ->execute();
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $value) {
            $value[self::OPTION_VALUE] = Sanitizer::maybeUnSerialize($value[self::OPTION_VALUE]);
            $this->options->set($value[self::OPTION_NAME], $value);
        }
        if ($stmt instanceof Statement) {
            $stmt->closeCursor();
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        if (!is_string($name)) {
            return false;
        }
        return $this->getDetail($name, true) !== true;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @param bool $autoload
     * @return mixed
     */
    public function getOrUpdate($name, $default, $autoload = false)
    {
        if ($this->has($name)) {
            return self::get($name);
        }
        $this->update($name, $default, $autoload);
        return $default;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if ($this->has($name)) {
            return $this->getDetail($name)[self::OPTION_VALUE];
        }

        return $default;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed|array
     */
    public function getDetail($name, $default = null)
    {
        if (!is_string($name)) {
            return null;
        }

        if ($this->options->has($name)) {
            if (! is_array($value = $this->options->get($name))) {
                return $default;
            }

            return $value;
        }

        $retVal = $this->createQueryBuilder()
                       ->select('*')
                       ->from($this->table)
                       ->where(self::OPTION_NAME . ' = :paramName')
                       ->setParameter(':paramName', $name)
                       ->execute()
                       ->fetch(\PDO::FETCH_ASSOC);
        if ($retVal) {
            $retVal[self::OPTION_VALUE] = Sanitizer::maybeUnSerialize($retVal[self::OPTION_VALUE]);
            $this->options->set($name, $retVal);
            return $retVal;
        }

        $this->options->set($name, true);
        return $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param null $autoload
     * @return bool|int|null
     */
    public function update($name, $value, $autoload = null)
    {
        if (!is_string($name)) {
            return null;
        }

        $exists = $this->options->has($name);
        if ($exists) {
            $exists = is_array($this->options->get($name));
        } else {
            $exists = is_array(self::getDetail($name));
        }

        $autoload = !$autoload || is_string($autoload) && !in_array(trim(strtolower($autoload)), ['yes', 'y'])
            ?  'no'
            : 'yes';

        if ($exists) {
            $qb = $this->createQueryBuilder()
                       ->update($this->table)
                       ->set(self::OPTION_VALUE, ':paramValue')
                       ->setParameter(':paramValue', Sanitizer::maybeSerialize($value));
            if (is_bool($autoload)) {
                $qb->set(self::OPTION_AUTOLOAD, ':paramAutoLoad');
                $qb->setParameter(':paramAutoLoad', $autoload);
            }
            $stmt = $qb->where(self::OPTION_NAME . '= :paramName')
                       ->setParameter(':paramName', $name)
                       ->execute();
            $opt = $this->options[$name];
            $opt[self::OPTION_VALUE]    = $value;
            $opt[self::OPTION_AUTOLOAD] = $autoload;
            $this->options[$name] = $opt;
            unset($opt);
            if ($stmt instanceof Statement) {
                $count = $stmt->rowCount();
                $stmt->closeCursor();
                $stmt = $count;
            }
            return $stmt;
        }

        $autoload = $autoload === true ? 'yes' : 'no';
        $qb = $this->createQueryBuilder()
                   ->insert($this->table)
                   ->values([
                       self::OPTION_NAME => ':paramName',
                       self::OPTION_VALUE => ':paramValue',
                       self::OPTION_AUTOLOAD => ':paramAutoLoad'
                   ])->setParameters(
                       [
                       ':paramName' => $name,
                       ':paramValue' => Sanitizer::maybeSerialize($value),
                       ':paramAutoLoad' => $autoload
                       ]
                   );
        $stmt = $qb->execute();
        self::getDetail($name);
        if ($stmt instanceof Statement) {
            $count = $stmt->rowCount();
            $stmt->closeCursor();
            $stmt = $count;
        }
        return $stmt;
    }

    /**
     * @param array $args
     * @return bool|int
     */
    public function updates(array $args)
    {
        if (empty($args)) {
            return false;
        }
        $success = 0;
        foreach ($args as $key => $value) {
            if ($this->update($key, $value)) {
                $success++;
            }
        }

        return $success;
    }

    /**
     * Delete options
     *
     * @param string $name
     */
    public function delete($name)
    {
        if (self::has($name)) {
            $stmt = $this->createQueryBuilder()
                         ->delete($this->table)
                         ->where(self::OPTION_NAME . ' = :paramName')
                         ->setParameter(':paramName', $name)
                         ->execute();
            if ($stmt instanceof Statement) {
                $stmt->closeCursor();
            }
            $this->options->set($name, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getDetail($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->update($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * Magic Method Destruct
     */
    public function __destruct()
    {
        $this->options->clear();
    }
}
