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

use PentagonalProject\Prima\App\Source\Model\User as SingleUser;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use Pentagonal\PhPass\PasswordHash;
use PentagonalProject\SlimService\Database;
use PentagonalProject\SlimService\Sanitizer;
use Slim\Collection;

/**
 * Class User
 * @package PentagonalProject\Prima\App\Source\Model\Database
 */
class User
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
     * User constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->db = $database;
        $this->table = $this->db->prefixTables(self::TABLE_NAME);
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
     * @param int $id
     *
     * @return null|SingleUser
     */
    public function getUserById(int $id)
    {
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
            return null;
        }

        foreach ($result as $key => $value) {
            $result[$key] = Sanitizer::maybeUnSerialize($value);
        }

        return new SingleUser($result, $this->db);
    }

    /**
     * @param string $email
     *
     * @return null|SingleUser
     */
    public function getUserByEmail(string $email)
    {
        $email = trim(strtolower($email));
        if (!$email) {
            return null;
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
            return null;
        }

        foreach ($result as $key => $value) {
            if ($key == self::COLUMN_PASSWORD) {
                if (!PasswordHash::isMaybeHash($value)) {
                    if (strlen($value) <> 64 || preg_match('/[^a-f0-9]/', $value)) {
                        $value = sha1($value);
                    }
                    $this
                        ->createQueryBuilder()
                        ->update($this->table)
                        ->set(self::COLUMN_PASSWORD, ':'.md5(self::COLUMN_PASSWORD))
                        ->set(self::COLUMN_UPDATED_AT, ':'.md5(self::COLUMN_UPDATED_AT))
                        ->where(self::COLUMN_ID, ':'. md5(self::COLUMN_ID))
                        ->setParameters([
                            ':' . md5(self::COLUMN_PASSWORD) => $value,
                            ':' . md5(self::COLUMN_UPDATED_AT) => $result[self::COLUMN_UPDATED_AT],
                            ':' . md5(self::COLUMN_ID) => $result[self::COLUMN_ID]
                        ])->execute();
                }

                $result[$key] = $value;
                continue;
            }

            $result[$key] = Sanitizer::maybeUnSerialize($value);
        }

        return new SingleUser($result, $this->db);
    }

    /**
     * @param string $username
     *
     * @return null|SingleUser
     */
    public function getUserByUserName(string $username)
    {
        $username = trim(strtolower($username));
        if (!$username) {
            return null;
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
            return null;
        }

        foreach ($result as $key => $value) {
            $result[$key] = Sanitizer::maybeUnSerialize($value);
        }

        return new SingleUser($result, $this->db);
    }

    /**
     * @param SingleUser $user
     *
     * @return bool|int
     */
    public function update(SingleUser $user)
    {
        $collection = $user->getNewCollection();
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

        $qb = $this
            ->createQueryBuilder()
            ->update($this->table);
        foreach ($data as $key => $value) {
            $keys = ':'.md5($key);
            $qb = $qb->set($key, $keys)->setParameter($keys, $value);
        }

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
            $keys = ':'.md5($key);
            $qb = $qb
                ->setValue($key, $keys)
                ->setParameter($keys, $value);
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

        return $this
            ->createQueryBuilder()
            ->delete($this->table)
            ->where(self::COLUMN_ID .'=:id')
            ->setParameter(':id', $userId)
            ->execute();
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
                $data[$key] = $passHash->hash(sha1($value));
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
}
