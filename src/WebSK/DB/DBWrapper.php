<?php

namespace WebSK\DB;

use WebSK\Slim\Container;

/**
 * Class DBWrapper
 * @package DB
 */
class DBWrapper
{
    /**
     * @return DBService
     */
    public static function getDBService(string $db_service_container_id)
    {
        $container = Container::self();

        /** @var DBService $db_service */
        return $container->get($db_service_container_id);
    }

    /**
     * @param string $db_service_container_id
     * @param string $query
     * @param array $params_arr
     * @return \PDOStatement
     * @throws \Exception
     */
    public static function query(string $db_service_container_id, string $query, $params_arr = array())
    {
        return self::getDBService($db_service_container_id)->query($query, $params_arr);
    }

    /**
     * @param string $db_service_container_id
     * @param string $query
     * @param array $params_arr
     * @param string $field_name_for_keys
     * @return array
     * @throws \Exception
     */
    public static function readObjects(string $db_service_container_id, string $query, array $params_arr = [], string $field_name_for_keys = '')
    {
        return self::getDBService($db_service_container_id)->readObjects($query, $params_arr, $field_name_for_keys);
    }

    /**
     * @param string $db_service_container_id
     * @param string $query
     * @param array $params_arr
     * @return mixed
     * @throws \Exception
     */
    public static function readObject(string $db_service_container_id, string $query, array $params_arr = [])
    {
        return self::getDBService($db_service_container_id)->readObject($query, $params_arr);
    }

    /**
     * @param string $db_service_container_id
     * @param string $query
     * @param array $params_arr
     * @return array
     * @throws \Exception
     */
    public static function readAssoc(string $db_service_container_id, string $query, array $params_arr = [])
    {
        return self::getDBService($db_service_container_id)->readAssoc($query, $params_arr);
    }

    /**
     * @param string $db_service_container_id
     * @param string $query
     * @param array $params_arr
     * @return array
     * @throws \Exception
     */
    public static function readColumn(string $db_service_container_id, string $query, array $params_arr = [])
    {
        return self::getDBService($db_service_container_id)->readColumn($query, $params_arr);
    }

    /**
     * @param string $db_service_container_id
     * @param string $query
     * @param array $params_arr
     * @return mixed
     * @throws \Exception
     */
    public static function readAssocRow(string $db_service_container_id, string $query, array $params_arr = [])
    {
        return self::getDBService($db_service_container_id)->readAssocRow($query, $params_arr);
    }

    /**
     * @param string $db_service_container_id
     * @param string $query
     * @param array $params_arr
     * @return false|mixed
     * @throws \Exception
     */
    public static function readField(string $db_service_container_id, string $query, array $params_arr = [])
    {
        return self::getDBService($db_service_container_id)->readField($query, $params_arr);
    }

    /**
     * @param string $db_service_container_id
     * @param string $db_sequence_name
     * @return string
     * @throws \Exception
     */
    public static function lastInsertId(string $db_service_container_id, string $db_sequence_name = '')
    {
        return self::getDBService($db_service_container_id)->lastInsertId($db_sequence_name);
    }
}
