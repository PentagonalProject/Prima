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

namespace PentagonalProject\Prima\App\Source\Model\Database;

use PentagonalProject\Prima\App\Source\Model\BaseDBModel;
use PentagonalProject\Prima\App\Source\Model\User as SingleUser;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use Pentagonal\PhPass\PasswordHash;
use PentagonalProject\SlimService\Database;
use Slim\Collection;

/**
 * Class User
 * @package PentagonalProject\Prima\App\Source\Model\Database
 */
class User extends BaseDBModel
{
    const TABLE_NAME = 'users';
    const COLUMN_ID = 'id';
    const COLUMN_FIRST_NAME = 'first_name';
    const COLUMN_LAST_NAME = 'last_name';
    const COLUMN_USERNAME = 'username';
    const COLUMN_EMAIL = 'email';
    const COLUMN_PASSWORD = 'password';
    const COLUMN_PRIVATE_KEY = 'private_key';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_UPDATED_AT = 'updated_at';

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * User constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->db = $database;
        $this->table = $this->db->prefixTables(self::TABLE_NAME);
        $this->collection = new Collection();
    }

    /**
     * @return Database
     */
    public function getDatabase() : Database
    {
        return $this->db;
    }

    /**
     * @return string
     */
    public function getTable() : string
    {
        return $this->table;
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() : QueryBuilder
    {
        return $this->db->createQueryBuilder();
    }

    /**
     * Make Password Hashed using md5
     *
     * @param string $pass
     *
     * @return string
     */
    protected function hashPlainPassword(string $pass) : string
    {
        return md5($pass);
    }

    /**
     * @param int $id
     *
     * @return null|SingleUser
     */
    public function getUserById(int $id)
    {
        $keyId = md5($id);
        if ($this->collection->has($keyId)) {
            $data = $this->collection[$keyId] ? $this->collection[$this->collection[$keyId]] : null;
            return is_array($data)
                ? new SingleUser($data, $this->db)
                : null;
        }

        $stmt = $this
            ->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where(self::COLUMN_ID . '=:id')
            ->setParameter(':id', $id)
            ->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($stmt instanceof Statement) {
            $stmt->closeCursor();
        }

        if (!is_array($result)) {
            return $this->collection[$keyId] = null;
        }

        return $this
            ->replaceCollectionData(
                new SingleUser($this->sanitizeForResult($result), $this->db)
            );
    }

    /**
     * @param string $email
     *
     * @return null|SingleUser
     */
    public function getUserByEmail(string $email)
    {
        $email = $this->trimmedLower($email);
        if ($email == '') {
            return null;
        }

        $keyId = $this->trimmedHash($email);
        if ($this->collection->has($keyId)) {
            $data = $this->collection[$keyId] ? $this->collection[$this->collection[$keyId]] : null;
            return is_array($data)
                ? new SingleUser($data, $this->db)
                : null;
        }

        $stmt = $this
            ->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('LOWER(TRIM('. self::COLUMN_EMAIL . '))=:email')
            ->setParameter(':email', $email)
            ->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($stmt instanceof Statement) {
            $stmt->closeCursor();
        }

        if (!is_array($result)) {
            return $this->collection[$keyId] = null;
        }

        return $this
            ->replaceCollectionData(
                new SingleUser($this->sanitizeForResult($result), $this->db)
            );
    }

    /**
     * @param string $username
     *
     * @return null|SingleUser
     */
    public function getUserByUserName(string $username)
    {
        $username = $this->trimmedLower($username);
        if ($username == '') {
            return null;
        }

        $keyId = $this->trimmedHash($username);
        if ($this->collection->has($keyId)) {
            $data = $this->collection[$keyId] ? $this->collection[$this->collection[$keyId]] : null;
            return is_array($data)
                ? new SingleUser($data, $this->db)
                : null;
        }

        $stmt = $this
            ->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('LOWER(TRIM('. self::COLUMN_USERNAME . '))=:username')
            ->setParameter(':username', $username)
            ->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($stmt instanceof Statement) {
            $stmt->closeCursor();
        }
        if (!is_array($result)) {
            return $this->collection[$keyId] = null;
        }

        // save to cached data
        return $this
            ->replaceCollectionData(
                new SingleUser($this->sanitizeForResult($result), $this->db)
            );
    }

    /**
     * @param SingleUser $user
     *
     * @return bool|int
     */
    public function update(SingleUser $user)
    {
        $collection = $user->getCollection();
        $userId = $user[self::COLUMN_ID];
        if (count($collection)  === 0 ||  !is_numeric($userId) || !is_int(abs($userId))) {
            return false;
        }
        $user = $this->getUserById((int) $userId);
        if (!$user instanceof  SingleUser) {
            return false;
        }

        $data = $this->sanitizeForDatabase($collection);
        if (empty($data)) {
            return 0;
        }
        if (! isset($data[self::COLUMN_UPDATED_AT])
            || $data[self::COLUMN_UPDATED_AT] !== $collection[self::COLUMN_UPDATED_AT]
        ) {
            $data[self::COLUMN_UPDATED_AT] = @gmdate('Y-m-d H:i:s');
        }

        $newCollection = $user->getCollection()->all();
        $qb = $this
            ->createQueryBuilder()
            ->update($this->table);
        foreach ($data as $key => $value) {
            $newCollection[$key] = $value;
            $param = ":{$this->createNameForParam($key)}";
            $qb = $qb->set($key, $param)->setParameter($param, $value);
        }

        // save to cached data
        $this->replaceCollectionData($user, $newCollection);

         return $qb
             ->where(self::COLUMN_ID .'=:id')
             ->setParameter(':id', $userId)
             ->execute();
    }

    /**
     * @param array $data
     *
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function create(array $data)
    {
        $mustBe = [
            self::COLUMN_USERNAME,
            self::COLUMN_FIRST_NAME,
            self::COLUMN_EMAIL,
            self::COLUMN_PASSWORD
        ];
        foreach ($mustBe as $value) {
            if (!isset($data[$value])) {
                throw new \RuntimeException(
                    sprintf(
                        'Column %s could not be empty.',
                        $value
                    )
                );
            }
        }

        unset($data[self::COLUMN_UPDATED_AT]);
        unset($data[self::COLUMN_CREATED_AT]);
        $newData = $this->sanitizeForDatabase(new Collection($data));
        unset($newData[self::COLUMN_ID]);
        if (!isset($newData[self::COLUMN_LAST_NAME])) {
            $newData[self::COLUMN_LAST_NAME] = '';
        }

        if (!isset($newData[self::COLUMN_PRIVATE_KEY])) {
            $newData[self::COLUMN_PRIVATE_KEY] = hash('sha512', microtime() . microtime());
        }

        $newData[self::COLUMN_CREATED_AT] = gmdate('Y-m-d H:i:s');
        $newData[self::COLUMN_UPDATED_AT] = '1990-01-01 00:00:00';
        if ($this->getUserByUserName($newData[self::COLUMN_USERNAME])) {
            throw new \RuntimeException(
                sprintf('Username %s has been exists!', $newData[self::COLUMN_USERNAME]),
                E_WARNING
            );
        }

        $qb = $this
            ->createQueryBuilder()
            ->insert($this->table);
        foreach ($newData as $key => $value) {
            $param = ":{$this->createNameForParam($key)}";
            $qb = $qb
                ->setValue($key, $param)
                ->setParameter($param, $value);
        }

        return $qb->execute();
    }

    /**
     * @param SingleUser $user
     *
     * @return bool|int
     */
    public function delete(SingleUser $user)
    {
        $userId = $user[self::COLUMN_ID];
        if (!is_numeric($userId) || !is_int(abs($userId))) {
            return false;
        }

        $user = $this->getUserById((int) $userId);
        if (!$user instanceof SingleUser) {
            return false;
        }

        $this->replaceCollectionData($user, null);

        return $this
            ->createQueryBuilder()
            ->delete($this->table)
            ->where(self::COLUMN_ID .'=:id')
            ->setParameter(':id', $userId)
            ->execute();
    }

    /**
     * @param SingleUser $user
     * @param mixed $value
     *
     * @return SingleUser
     */
    protected function replaceCollectionData(SingleUser $user, $value = null) : SingleUser
    {
        if (func_num_args() < 2) {
            $value = $user->getCollection()->all();
        } elseif ($value instanceof SingleUser) {
            $value = $value->getCollection()->all();
        }

        // only null & array set cached
        $value =  is_array($value) ? $value : null;
        $userId = $this->trimmedHash($user[self::COLUMN_ID]);
        $this->collection->replace([
            $userId => $value,
            $this->trimmedHash($user[self::COLUMN_USERNAME]) => $userId,
            $this->trimmedHash($user[self::COLUMN_EMAIL]) => $userId,
        ]);

        return $user;
    }

    /**
     * @param string|int $value
     *
     * @return string
     */
    private function trimmedLower($value) : string
    {
        return trim(strtolower($value));
    }

    /**
     * @param string|int $value
     *
     * @return string
     */
    private function trimmedHash($value) : string
    {
        return md5($this->trimmedLower($value));
    }

    /**
     * @param Collection $collection
     *
     * @return array
     */
    private function sanitizeForDatabase(Collection $collection) : array
    {
        $fillAble = [
            self::COLUMN_FIRST_NAME,
            self::COLUMN_LAST_NAME,
            self::COLUMN_USERNAME,
            self::COLUMN_EMAIL,
            self::COLUMN_PASSWORD,
            self::COLUMN_PRIVATE_KEY,
            self::COLUMN_CREATED_AT,
        ];

        $data = [];
        foreach ($fillAble as $key) {
            if (!$collection->has($key)) {
                continue;
            }

            $value = $collection->get($key);
            if ($key === self::COLUMN_PASSWORD) {
                $value = !is_string($value) ? serialize($value) : $value;
                $passHash = new PasswordHash();
                $data[$key] = $passHash->hash($this->hashPlainPassword($value));
                continue;
            }
            if ($key === self::COLUMN_EMAIL) {
                if (!is_string($value)) {
                    throw new \RuntimeException(
                        sprintf(
                            'Invalid email, email must be as a string %s given.',
                            gettype($value)
                        )
                    );
                }
                $value = filter_var($value, FILTER_VALIDATE_EMAIL);
                if ($value == false) {
                    throw new \RuntimeException(
                        'Invalid email email address to update.',
                        E_WARNING
                    );
                }
                $data[$key] = strtolower($value);
                continue;
            }
            if ($value === self::COLUMN_USERNAME) {
                if (!is_string($value)) {
                    throw new \RuntimeException(
                        'Username must be as a string.',
                        E_WARNING
                    );
                }
                if (strlen(trim($value)) < 3) {
                    throw new \RuntimeException(
                        'Invalid username, username must be more 3 characters or more.',
                        E_WARNING
                    );
                }

                $value = strtolower(trim($value));
                if (preg_match('/[^a-z0-9]/', $value)) {
                    throw new \RuntimeException(
                        'Invalid username, username only contains alpha numeric characters.',
                        E_WARNING
                    );
                }
                $data[$key] = $value;
                continue;
            }
            if ($key == self::COLUMN_CREATED_AT) {
                $value = @strtotime($value);
                if (!$value) {
                    continue;
                }
                $data[$key] = date('Y-m-d H:i:s', $value);
                continue;
            }
            if (!is_string($value)) {
                throw new \RuntimeException(
                    sprintf(
                        'Invalid %1$s, %1$s must be as a string %2$s given.',
                        $key,
                        gettype($value)
                    )
                );
            }

            $data[$key] = trim($value);
            if ($key === self::COLUMN_PRIVATE_KEY && strlen($data[$key]) <> 128) {
                if (strlen($data[$key]) < 128) {
                    $data[$key] .= substr(hash('sha512', microtime()), 0, 128-strlen($data[$key]));
                } else {
                    $data[$key] = substr($data[$key], 0, 128);
                }
            }
        }

        return $data;
    }

    /**
     * @param array $result
     *
     * @return array
     */
    private function sanitizeForResult(array $result) : array
    {
        foreach ($result as $key => $value) {
            if ($key == self::COLUMN_PASSWORD) {
                if (!PasswordHash::isMaybeHash($value)) {
                    if (strlen($value) <> 64 || preg_match('/[^a-f0-9]/', $value)) {
                        $value = $this->hashPlainPassword($value);
                    }
                    $passHash = new PasswordHash();
                    $value = $passHash->hash($value);
                    $stmt = $this
                        ->createQueryBuilder()
                        ->update($this->table)
                        ->set(self::COLUMN_PASSWORD, ':pass')
                        ->set(self::COLUMN_UPDATED_AT, ':update')
                        ->where(self::COLUMN_ID . '=:id')
                        ->setParameters([
                            ':pass'   => $value,
                            ':update' => $result[self::COLUMN_UPDATED_AT],
                            ':id'     => $result[self::COLUMN_ID]
                        ])->execute();

                    if ($stmt instanceof Statement) {
                        $stmt->closeCursor();
                    }
                }

                $result[$key] = $value;
                continue;
            }

            $result[$key] = $this->resolveResult($value);
        }

        return $result;
    }
}
