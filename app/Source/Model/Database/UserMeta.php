<?php
namespace PentagonalProject\Prima\App\Source\Model\Database;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use PentagonalProject\SlimService\Database;
use PentagonalProject\SlimService\Sanitizer;
use PentagonalProject\Prima\App\Source\Model\User as SingleUser;

/**
 * Class UserMeta
 * @package PentagonalProject\Prima\App\Source\Model\Database
 */
class UserMeta
{
    const TABLE_NAME     = 'users_meta';
    const COLUMN_ID      = 'meta_id';
    const COLUMN_USER_ID = 'meta_user_id';
    const COLUMN_NAME    = 'meta_name';
    const COLUMN_VALUE   = 'meta_value';

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * UserMeta constructor.
     *
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->table = $db->prefixTables(self::TABLE_NAME);
        $this->db = $db;
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() : QueryBuilder
    {
        return $this->db->createQueryBuilder();
    }

    /**
     * @return string
     */
    public function getTable() : string
    {
        return $this->table;
    }

    /**
     * @return Database
     */
    public function getDatabase() : Database
    {
        return $this->db;
    }

    /**
     * @param SingleUser $user
     *
     * @return null|array
     */
    public function getUserMetaByUser(SingleUser $user)
    {
        $id = $user[User::COLUMN_ID];
        $stmt = $this
            ->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where(self::COLUMN_USER_ID . ':id')
            ->setParameter(':id', $id)
            ->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($stmt instanceof Statement) {
            $stmt->closeCursor();
        }

        if (empty($result)) {
            return null;
        }
        foreach ($result as $key => $res) {
            foreach ($res as $k => $r) {
                if (strtolower($k) === self::COLUMN_VALUE) {
                    $res[$k] = Sanitizer::maybeUnSerialize($r);
                }
            }

            $result[$key] = $res;
        }

        return $result;
    }

    /**
     * @param string $name
     * @param $value
     * @param SingleUser $user
     *
     * @return int|null
     */
    public function update(string $name, $value, SingleUser $user)
    {
        $id = $user[User::COLUMN_ID];
        if (!is_numeric($id) || !is_int(abs($id))) {
            return null;
        }

        $value = Sanitizer::maybeSerialize($value);
        if (($meta = $this->getUserMeta($name, $user))) {
            return $this
                ->createQueryBuilder()
                ->update($this->table)
                ->set(self::COLUMN_VALUE, ':value')
                ->andWhere(self::COLUMN_USER_ID .'=:id')
                ->andWhere(self::COLUMN_NAME . '=:name')
                ->setParameters([
                    ':value' => $value,
                    ':id' => $id,
                    ':name' => $name,
                ])->execute();
        }

        return $this
            ->createQueryBuilder()
            ->insert($this->table)
            ->values([
                self::COLUMN_USER_ID => ':id',
                self::COLUMN_NAME => ':name',
                self::COLUMN_VALUE => ':val'
            ])
            ->setParameters([
                ':id' => $id,
                ':name' => $name,
                ':val' => $value
            ])
            ->execute();
    }

    /**
     * @param string $name
     * @param SingleUser $user
     *
     * @return int|null
     */
    public function delete(string $name, SingleUser $user)
    {
        $id = $user[User::COLUMN_ID];
        if (!is_numeric($id) || !is_int(abs($id))) {
            return null;
        }
        return $this
            ->createQueryBuilder()
            ->delete($this->table)
            ->where(self::COLUMN_USER_ID . '=:id')
            ->andWhere(self::COLUMN_NAME. '=:name')
            ->setParameters([
                ':id' => $id,
                ':name' => $name
            ])->execute();
    }

    /**
     * @param string $name
     * @param SingleUser $user
     *
     * @return null|array
     */
    public function getUserMeta(string $name, SingleUser $user)
    {
        $id = $user[User::COLUMN_ID];
        if (! is_numeric($id) || ! is_int(abs($id))) {
            return null;
        }
        $stmt   = $this
            ->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where(self::COLUMN_NAME . '=:name')
            ->andWhere(self::COLUMN_USER_ID . '=:id')
            ->setParameter(':name', $name)
            ->setParameter(':id', $id)
            ->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($stmt instanceof Statement) {
            $stmt->closeCursor();
        }

        if (! is_array($result) || empty($result)) {
            return null;
        }
        foreach ($result as $key => $value) {
            if (strtolower($key) === self::COLUMN_VALUE) {
                $result[$key] = Sanitizer::maybeUnSerialize($value);
            }
        }

        return $result;
    }

    /**
     * @param string $name
     * @param SingleUser $user
     *
     * @return mixed|null
     */
    public function getUserMetaValue(string $name, SingleUser $user)
    {
        $result = $this->getUserMeta($name, $user);
        if (is_array($result) && array_key_exists(self::COLUMN_VALUE, $result)) {
            return $result[self::COLUMN_VALUE];
        }

        return null;
    }
}
