<?php

namespace WebSK\DB;

/**
 * Class DBWrapper
 * @package WebSK\DB
 */
class DBWrapper
{
    protected static DBService $db_service;

    /**
     * @return DBService
     */
    public static function getDBService(): DBService
    {
        return self::$db_service;
    }

    /**
     * @param DBService $db_service
     */
    public static function setDbService(DBService $db_service): void
    {
        self::$db_service = $db_service;
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return \PDOStatement
     * @throws \Exception
     */
    public static function query(string $query, array $params_arr = []): \PDOStatement
    {
        return self::getDBService()->query($query, $params_arr);
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @param string $field_name_for_keys
     * @return array
     * @throws \Exception
     */
    public static function readObjects(string $query, array $params_arr = [], string $field_name_for_keys = ''): array
    {
        return self::getDBService()->readObjects($query, $params_arr, $field_name_for_keys);
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return mixed
     * @throws \Exception
     */
    public static function readObject(string $query, array $params_arr = [])
    {
        return self::getDBService()->readObject($query, $params_arr);
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return array
     * @throws \Exception
     */
    public static function readAssoc(string $query, array $params_arr = []): array
    {
        return self::getDBService()->readAssoc($query, $params_arr);
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return array
     * @throws \Exception
     */
    public static function readColumn(string $query, array $params_arr = []): array
    {
        return self::getDBService()->readColumn($query, $params_arr);
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return mixed
     * @throws \Exception
     */
    public static function readAssocRow(string $query, array $params_arr = [])
    {
        return self::getDBService()->readAssocRow($query, $params_arr);
    }

    /**
     * @param string $query
     * @param array $params_arr
     * @return false|mixed
     * @throws \Exception
     */
    public static function readField(string $query, array $params_arr = [])
    {
        return self::getDBService()->readField($query, $params_arr);
    }

    /**
     * @param string $db_sequence_name
     * @return string
     * @throws \Exception
     */
    public static function lastInsertId(string $db_sequence_name = ''): string
    {
        return self::getDBService()->lastInsertId($db_sequence_name);
    }
}
