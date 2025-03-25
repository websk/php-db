<?php

namespace WebSK\DB;

/**
 * Class DBService
 * @package WebSK\DB
 */
class DBService
{
    protected DB $db_connections;

    protected DBSettings $db_settings;

    protected DBConnectorInterface $db_connector;

    /**
     * DBService constructor.
     * @param DBConnectorInterface $db_connector
     * @param DBSettings $db_settings
     */
    public function __construct(DBConnectorInterface $db_connector, DBSettings $db_settings)
    {
        $this->db_connector = $db_connector;
        $this->db_settings = $db_settings;
    }

    /**
     * @return DB
     * @throws \Exception
     */
    public function getDB(): DB
    {
        if (isset($this->db_connections)) {
            return $this->db_connections;
        }

        $this->db_connections = new DB($this->db_connector);

        return $this->db_connections;
    }

    /**
     * @return DBSettings
     */
    public function getDbSettings(): DBSettings
    {
        return $this->db_settings;
    }

    /**
     * @return string
     */
    public function getDbName(): string
    {
        return $this->db_connector->getDbName();
    }

    /**
     *
     * @param string $query
     * @param array $params_arr
     * @return \PDOStatement
     * @throws \Exception
     */
    public function query(string $query, array $params_arr = []): \PDOStatement
    {
        $db_obj = $this->getDB();
        if (!$db_obj) {
            throw new \Exception('getDB failed');
        }

        try {
            return $db_obj->query($query, $params_arr);
        } catch (\PDOException $e) {
            $uri = '';

            // may be not present in command line scripts
            if (array_key_exists('REQUEST_URI', $_SERVER)) {
                $uri = "\r\nUrl: " . $_SERVER['REQUEST_URI'];
            }

            throw new \PDOException($uri . "\r\n" . $e->getMessage());
        }
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @param string $field_name_for_keys
     * @return array
     * @throws \Exception
     */
    public function readObjects(string $query, array $params_arr = [], string $field_name_for_keys = ''): array
    {
        $statement_obj = $this->query($query, $params_arr);

        $output_arr = [];

        while (($row_obj = $statement_obj->fetchObject()) !== false) {
            if ($field_name_for_keys != '') {
                $key = $row_obj->$field_name_for_keys;
                $output_arr[$key] = $row_obj;
            } else {
                $output_arr[] = $row_obj;
            }
        }

        return $output_arr;
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return mixed
     * @throws \Exception
     */
    public function readObject(string $query, array $params_arr = [])
    {
        $statement_obj = $this->query($query, $params_arr);

        return $statement_obj->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return array
     * @throws \Exception
     */
    public function readColumn(string $query, array $params_arr = []): array
    {
        $statement_obj = $this->query($query, $params_arr);

        $output_arr = [];

        while (($field = $statement_obj->fetch(\PDO::FETCH_COLUMN)) !== false) {
            $output_arr[] = $field;
        }

        return $output_arr;
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return array
     * @throws \Exception
     */
    public function readAssoc(string $query, array $params_arr = []): array
    {
        $statement_obj = $this->query($query, $params_arr);

        $output_arr = [];

        while (($row_arr = $statement_obj->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $output_arr[] = $row_arr;
        }

        return $output_arr;
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return mixed
     * @throws \Exception
     */
    public function readAssocRow(string $query, array $params_arr = [])
    {
        $statement_obj = $this->query($query, $params_arr);

        return $statement_obj->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return mixed|false
     * Возвращает false при ошибке, или если нет записей.
     * @throws \Exception
     */
    public function readField(string $query, array $params_arr = [])
    {
        $statement_obj = $this->query($query, $params_arr);
        return $statement_obj->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @param ?string $db_sequence_name
     * @return string
     * @throws \Exception
     */
    public function lastInsertId(?string $db_sequence_name = null): string
    {
        $db_obj = $this->getDB();
        if (!$db_obj) {
            throw new \Exception('getDB failed');
        }

        return $db_obj->lastInsertId($db_sequence_name);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function beginTransaction(): bool
    {
        $db_obj = $this->getDB();
        if (!$db_obj) {
            throw new \Exception('getDB failed');
        }

        return $db_obj->beginTransaction();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function inTransaction(): bool
    {
        $db_obj = $this->getDB();
        if (!$db_obj) {
            throw new \Exception('getDB failed');
        }

        return $db_obj->inTransaction();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function commitTransaction(): bool
    {
        $db_obj = $this->getDB();
        if (!$db_obj) {
            throw new \Exception('getDB failed');
        }

        return $db_obj->commit();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function rollBackTransaction(): bool
    {
        $db_obj = $this->getDB();
        if (!$db_obj) {
            throw new \Exception('getDB failed');
        }

        return $db_obj->rollBack();
    }
}
