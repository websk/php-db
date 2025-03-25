<?php

namespace WebSK\DB;

/**
 * Class DB
 * @package WebSK\DB
 * Represents a single database connection.
 */
class DB
{
    protected ?\PDO $pdo_obj = null;

    /**
     * DB constructor.
     * Умеет использовать объект PDO из указанного DBConnector
     * (при этом можно использовать одно подключение для нескольких объектов БД
     * если они смотрят на одну физическую базу, чтобы правильно работали транзакции)
     * @param DBConnectorInterface $db_connector_obj
     * @throws \Exception
     */
    public function __construct(DBConnectorInterface $db_connector_obj)
    {
        $this->setPdoObj($db_connector_obj->getPdoObj());
    }

    /**
     * Throws PDOException on failure.
     * @param string $query
     * @param array $params_arr
     * @return \PDOStatement
     * @throws \Exception
     */
    public function query(string $query, array $params_arr = []): \PDOStatement
    {
        $statement_obj = $this->getPdoObj()->prepare($query);

        foreach ($params_arr as $key => $param_value) {
            if (is_object($param_value)) {
                throw new \Exception($key . ' passed object');
            }

            if (is_int($param_value)) {
                $param_type = \PDO::PARAM_INT;
            } elseif (is_bool($param_value)) {
                $param_type = \PDO::PARAM_INT; // https://bugs.php.net/bug.php?id=38546
            } elseif (is_null($param_value)) {
                $param_type = \PDO::PARAM_NULL;
            } elseif (is_string($param_value)) {
                $param_type = \PDO::PARAM_STR;
            } elseif (is_float($param_value)) {
                $param_type = \PDO::PARAM_STR;
            } else {
                throw new \Exception('unknown param type');
            }

            if (is_int($key)) {
                $key = $key + 1; // For a prepared statement using question mark placeholders, this will be the 1-indexed position of the parameter.
            }

            $statement_obj->bindValue($key, $param_value, $param_type);
        }

        if (!$statement_obj->execute()) {
            throw new \Exception('query execute failed');
        }

        return $statement_obj;
    }

    /**
     * @param ?string $db_sequence_name
     * @return string
     */
    public function lastInsertId(?string $db_sequence_name = null): string
    {
        return $this->getPdoObj()->lastInsertId($db_sequence_name);
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->getPdoObj()->inTransaction();
    }

    /**
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->getPdoObj()->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        return $this->getPdoObj()->commit();
    }

    /**
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->getPdoObj()->rollBack();
    }

    /**
     * @return null|\PDO
     */
    public function getPdoObj(): ?\PDO
    {
        return $this->pdo_obj;
    }

    /**
     * @param null|\PDO $pdo_obj
     */
    public function setPdoObj(?\PDO $pdo_obj): void
    {
        $this->pdo_obj = $pdo_obj;
    }
}
