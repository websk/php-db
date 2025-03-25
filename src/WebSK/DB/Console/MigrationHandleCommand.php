<?php

namespace WebSK\DB\Console;

use GetOpt\Command;
use WebSK\DB\DBServiceFactory;

/**
 * Class MigrationHandleCommand
 * @package WebSK\DB\Console
 */
class MigrationHandleCommand extends Command
{
    const string NAME = 'migrations:migration_handle';

    const string COMMAND_SKIP_QUERY = 's';
    const string COMMAND_IGNORE_QUERY = 'i';

    protected array $db_settings_arr = [];

    /**
     * MigrationCommand constructor.
     * @param array $db_settings_arr
     */
    public function __construct(array $db_settings_arr)
    {
        $this->db_settings_arr = $db_settings_arr;

        parent::__construct(self::NAME, [$this, 'execute']);
    }

    public function execute(): void
    {
        if (empty($this->db_settings_arr)) {
            throw new \Exception(
                'No database entries in config'
            );
        }

        foreach ($this->db_settings_arr as $db_id => $db_config) {
            echo "Database ID in application config: " . $db_id . PHP_EOL;

            if (!isset($db_config['dump_file_path'])) {
                echo "Unknown dump_file_path in DB config: " . $db_id . PHP_EOL;
            }

            $migration_service = new MigrationService(
                DBServiceFactory::factoryMySQL($db_config)
            );

            $this->handleProcessDB($migration_service);
        }

        echo "All queries executed, press ENTER to continue" . PHP_EOL;
    }

    /**
     * @param MigrationService $migration_service
     * @throws \Exception
     */
    protected function handleProcessDB(MigrationService $migration_service): void
    {
        $error = $migration_service->checkConnectDBAndReturnError();
        if ($error != '') {
            throw new \Exception($error);
        }

        $executed_queries_sql_arr = [];
        try {
            $executed_queries_sql_arr = $migration_service->getExecutedQueriesArr();
        } catch (\Exception $e) {
            echo $this->delimiter();
            echo "Can not load the executed queries list from " . MigrationService::EXECUTED_QUERIES_TABLE_NAME . " table:" . PHP_EOL;
            echo $e->getMessage() . PHP_EOL . PHP_EOL;

            echo "Probably the " . MigrationService::EXECUTED_QUERIES_TABLE_NAME . " table was not created. Choose:" . PHP_EOL;
            echo "\tENTER to create table and proceed" . PHP_EOL;
            echo "\tany other key to exit" . PHP_EOL;

            $command_str = $this->readStdinAnswer();

            if ($command_str == '') {
                $migration_service->createMigrationTable();
            } else {
                exit;
            }
        }

        $queries_arr = $migration_service->loadQueriesArrForDB();

        foreach ($queries_arr as $query) {
            if (in_array($query, $executed_queries_sql_arr)) {
                continue;
            }

            echo $this->delimiter();
            echo $query . "\n";

            echo PHP_EOL;
            echo "\t" . self::COMMAND_SKIP_QUERY . ": skip query now, do not mark as executed" . PHP_EOL;
            echo "\t" . self::COMMAND_IGNORE_QUERY . ": ignore query - mark as executed, but do not execute (you can execute one manually)" . PHP_EOL;
            echo "\tENTER execute query" . PHP_EOL;

            $command_str = $this->readStdinAnswer();

            switch ($command_str) {
                case '':
                    $migration_service->executeMigration($query);
                    echo "Query executed." . PHP_EOL;
                    break;
                case self::COMMAND_IGNORE_QUERY:
                    $migration_service->markAsExecutedMigration($query);
                    echo "Query marked as executed without execution." . PHP_EOL;
                    break;

                case self::COMMAND_SKIP_QUERY:
                    echo "Query skipped." . PHP_EOL;
                    break;

                default:
                    throw new \Exception('unknown command');
                    break;
            }
        }
    }

    /**
     * @return string
     */
    protected function delimiter(): string
    {
        return str_pad('', 60, '_') . PHP_EOL . PHP_EOL;
    }

    /**
     * @return string
     */
    protected function readStdinAnswer(): string
    {
        echo "> ";

        return trim(fgets(STDIN));
    }
}
