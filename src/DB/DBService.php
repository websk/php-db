<?php

namespace Websk\DB;

class DBService
{
    /** @var DB */
    protected $db_connections;
    /** @var DBSettings */
    protected $db_settings;
    /** @var DBConnectorInterface */
    protected $db_connector;

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
    public function getDB()
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
    public function getDbName()
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
    public function query($query, $params_arr = [])
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
     * @param $query
     * @param array $params_arr
     * @param string $field_name_for_keys
     * @return array
     * @throws \Exception
     */
    public function readObjects($query, $params_arr = [], $field_name_for_keys = '')
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
     * @param $query
     * @param array $params_arr
     * @return mixed
     * @throws \Exception
     */
    public function readObject($query, $params_arr = [])
    {
        $statement_obj = $this->query($query, $params_arr);

        return $statement_obj->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * @param $query
     * @param array $params_arr
     * @return array
     * @throws \Exception
     */
    public function readColumn($query, $params_arr = [])
    {
        $statement_obj = $this->query($query, $params_arr);

        $output_arr = [];

        while (($field = $statement_obj->fetch(\PDO::FETCH_COLUMN)) !== false) {
            $output_arr[] = $field;
        }

        return $output_arr;
    }

    /**
     * @param $query
     * @param array $params_arr
     * @return mixed
     * @throws \Exception
     */
    public function readAssocRow($query, $params_arr = [])
    {
        $statement_obj = $this->query($query, $params_arr);

        return $statement_obj->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $query
     * @param array $params_arr
     * @return mixed|false
     * Возвращает false при ошибке, или если нет записей.
     * @throws \Exception
     */
    public function readField($query, $params_arr = [])
    {
        $statement_obj = $this->query($query, $params_arr);
        return $statement_obj->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @param $db_sequence_name
     * @return string
     * @throws \Exception
     */
    public function lastInsertId($db_sequence_name)
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
    public function beginTransaction()
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
    public function inTransaction()
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
    public function commitTransaction()
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
    public function rollBackTransaction()
    {
        $db_obj = $this->getDB();
        if (!$db_obj) {
            throw new \Exception('getDB failed');
        }

        return $db_obj->rollBack();
    }
}
