<?php

namespace WebSK\DB;

/**
 * Class DBServiceFactory
 * @package WebSK\DB
 */
class DBServiceFactory
{
    /**
     * @param array $db_config
     * @return DBService
     */
    public static function factoryMySQL(array $db_config): DBService
    {
        $db_connector = new DBConnectorMySQL(
            $db_config['host'],
            $db_config['db_name'],
            $db_config['user'],
            $db_config['password']
        );

        $db_settings = new DBSettings(
            'mysql',
            $db_config['dump_file_path']
        );

        return new DBService($db_connector, $db_settings);
    }
}