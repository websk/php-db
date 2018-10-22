<?php

namespace Websk\DB;

/**
 * Class DB
 * @package DB
 * Represents a single database connection.
 */
class DB
{
    /**
     * Throws PDOException on failure.
     * @var \PDO|null
     */
    protected $pdo_obj = null;

    /**
     * DB constructor.
     * Умеет ииспользовать объект PDO из указанного DBConnector
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
     * @param $query
     * @param array $params_arr
     * @return \PDOStatement
     * @throws \Exception
     */
    public function query($query, $params_arr = array())
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
     * @param string|null $db_sequence_name
     * @return string
     */
    public function lastInsertId(string $db_sequence_name = null)
    {
        return $this->getPdoObj()->lastInsertId($db_sequence_name);
    }

    /**
     * @return bool
     */
    public function inTransaction()
    {
        return $this->getPdoObj()->inTransaction();
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->getPdoObj()->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->getPdoObj()->commit();
    }

    /**
     * @return bool
     */
    public function rollBack()
    {
        return $this->getPdoObj()->rollBack();
    }

    /**
     * @return null|\PDO
     */
    public function getPdoObj()
    {
        return $this->pdo_obj;
    }

    /**
     * @param null|\PDO $pdo_obj
     */
    public function setPdoObj($pdo_obj)
    {
        $this->pdo_obj = $pdo_obj;
    }
}
