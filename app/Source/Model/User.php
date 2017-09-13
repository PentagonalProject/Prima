<?php
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
