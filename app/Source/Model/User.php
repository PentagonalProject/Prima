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

use Pentagonal\PhPass\PasswordHash;
use PentagonalProject\Prima\App\Source\Model\Database\User as UserDB;
use PentagonalProject\Prima\App\Source\Model\Database\UserMeta;
use PentagonalProject\SlimService\Database;
use Slim\Collection;

/**
 * Class User
 * @package PentagonalProject\Prima\App\Source\Model
 */
class User implements \ArrayAccess
{
    /**
     * @var Database
     */
    protected $db;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Collection
     */
    protected $newCollection;

    /**
     * User constructor.
     *
     * @param array $detail
     * @param Database $db
     */
    public function __construct(array $detail, Database $db)
    {
        $this->db = $db;
        $this->collection = new Collection($detail);
        $this->newCollection = new Collection();
    }

    public function getDatabase() : Database
    {
        return $this->db;
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return $this->collection->has($offset);
    }

    /**
     * @param mixed $offset
     * no operation
     */
    public function offsetUnset($offset)
    {
        unset($this->newCollection[$offset]);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * no operation
     */
    public function offsetSet($offset, $value)
    {
        // no op
        $this->newCollection[$offset] = $value;
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->collection->get($offset);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->collection->get($name);
    }

    /**
     * @param mixed $name
     */
    public function __unset($name)
    {
        unset($this->newCollection[$name]);
    }

    /**
     * @return Collection
     */
    public function getNewCollection() : Collection
    {
        return $this->collection;
    }

    /**
     * @return bool|int
     */
    public function update()
    {
        return $this->createObjectUser()->update($this);
    }

    /**
     * @return UserDB
     */
    public function createObjectUser() : UserDB
    {
        return new UserDB($this->db);
    }

    /**
     * @return UserMeta
     */
    public function createObjectMeta() : UserMeta
    {
        return new UserMeta($this->db);
    }

    /**
     * @param string $plain
     *
     * @return bool
     */
    public function isPasswordMatch(string $plain) : bool
    {
        $password = $this[UserDB::COLUMN_PASSWORD];
        if (!is_string($password)) {
            return false;
        }

        $passwordHash = new PasswordHash();
        return $passwordHash->verify(sha1($plain), $password);
    }
}
