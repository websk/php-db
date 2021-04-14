<?php

namespace WebSK\DB;

/**
 * Class DBServiceFactory
 * @package WebSK\DB
 */
class DBServiceFactory
{

    protected static array $db_connector_objs_arr = [];

    /**
     * @param array $db_config
     * @return DBService
     */
    public static function factoryMySQL(array $db_config): DBService
    {
        $db_settings = new DBSettings(
            'mysql',
            $db_config['dump_file_path']
        );

        return new DBService(
            self::getDBConnectorMySQL($db_config),
            $db_settings
        );
    }

    /**
     * @param array $db_config
     * @return DBConnectorMySQL
     */
    protected static function getDBConnectorMySQL(array $db_config) : DBConnectorMySQL
    {
        $db_connector_hash = md5(
            $db_config['host'] . $db_config['db_name'] . $db_config['user'] . $db_config['password']
        );

        if (isset(self::$db_connector_objs_arr[$db_connector_hash])
            && self::$db_connector_objs_arr[$db_connector_hash] instanceof DBConnectorMySQL
        ) {
            return self::$db_connector_objs_arr[$db_connector_hash];
        }

        self::$db_connector_objs_arr[$db_connector_hash] = new DBConnectorMySQL(
            $db_config['host'],
            $db_config['db_name'],
            $db_config['user'],
            $db_config['password']
        );

        return self::$db_connector_objs_arr[$db_connector_hash];
    }
}