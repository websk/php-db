<?php

namespace WebSK\DB\Console;

use GetOpt\Command;
use WebSK\DB\DBServiceFactory;
use WebSK\Utils\Assert;

/**
 * Class MigrationAutoCommand
 * @package WebSK\DB\Console
 */
class MigrationAutoCommand extends Command
{
    const NAME = 'migrations:migration_auto';

    protected array $db_settings_arr = [];

    /**
     * MigrationAutoCommand constructor.
     * @param array $db_settings_arr
     */
    public function __construct(array $db_settings_arr)
    {
        $this->db_settings_arr = $db_settings_arr;

        parent::__construct(self::NAME, [$this, 'execute']);
    }

    public function execute()
    {
        Assert::assert(!empty($this->db_settings_arr), 'No database entries in config');

        foreach ($this->db_settings_arr as $db_id => $db_config) {
            echo "Database ID in application config: " . $db_id . "\n";

            if (!isset($db_config['dump_file_path'])) {
                echo "Unknown dump_file_path in DB config: " . $db_id . "\n";
            }

            $migration_service = new MigrationService(
                DBServiceFactory::factoryMySQL($db_config)
            );

            $this->autoProcessDB($migration_service);
        }
    }

    /**
     * @param MigrationService $migration_service
     * @throws \Exception
     */
    protected function autoProcessDB(MigrationService $migration_service)
    {
        $error = $migration_service->checkConnectDBAndReturnError();
        if ($error != '') {
            throw new \Exception($error);
        }

        $executed_queries_sql_arr = [];
        try {
            $executed_queries_sql_arr = $migration_service->getExecutedQueriesArr();
        } catch (\Exception $e) {
            $migration_service->createMigrationTable();
        }

        $queries_arr = $migration_service->loadQueriesArrForDB();

        foreach ($queries_arr as $query) {
            if (in_array($query, $executed_queries_sql_arr)) {
                continue;
            }

            $migration_service->executeMigration($query);

            echo "Query executed: " . $query . " \n";
        }
    }
}
