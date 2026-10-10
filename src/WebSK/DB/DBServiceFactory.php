<?php

namespace WebSK\DB;

/**
 * Class DBServiceFactory
 * @package WebSK\DB
 */
class DBServiceFactory
{

    /** @var array<string, DBConnectorMySQL> */
    protected static array $db_connector_objs_arr = [];

    /**
     * @param array<string, string> $db_config
     * @return DBService
     */
    public static function factoryMySQL(array $db_config): DBService
    {
        self::validateMySQLConfig($db_config);

        $db_settings = new DBSettings(
            'mysql',
            $db_config['dump_file_path'] ?? ''
        );

        return new DBService(
            self::getDBConnectorMySQL($db_config),
            $db_settings
        );
    }

    /**
     * @param array<string, string> $db_config
     * @return DBConnectorMySQL
     */
    protected static function getDBConnectorMySQL(array $db_config) : DBConnectorMySQL
    {
        $db_connector_hash = md5(
            $db_config['host'] . $db_config['db_name'] . $db_config['user'] . $db_config['password']
        );

        if (isset(self::$db_connector_objs_arr[$db_connector_hash])) {
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

    /**
     * @param array<string, string> $db_config
     */
    protected static function validateMySQLConfig(array $db_config): void
    {
        foreach (['host', 'db_name', 'user', 'password'] as $required_key) {
            if (!array_key_exists($required_key, $db_config)) {
                throw new \InvalidArgumentException('Missing database config key: ' . $required_key);
            }
        }
    }
}
