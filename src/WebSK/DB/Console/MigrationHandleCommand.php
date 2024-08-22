<?php

namespace WebSK\DB\Console;

use GetOpt\Command;
use WebSK\DB\DBServiceFactory;
use WebSK\Utils\Assert;

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

            $this->handleProcessDB($migration_service);
        }

        echo "All queries executed, press ENTER to continue\n";
    }

    /**
     * @param MigrationService $migration_service
     * @throws \Exception
     */
    protected function handleProcessDB(MigrationService $migration_service)
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
            echo "Can not load the executed queries list from " . MigrationService::EXECUTED_QUERIES_TABLE_NAME . " table:\n";
            echo $e->getMessage() . "\n\n";

            echo "Probably the " . MigrationService::EXECUTED_QUERIES_TABLE_NAME . " table was not created. Choose:\n";
            echo "\tENTER to create table and proceed\n";
            echo "\tany other key to exit\n";

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

            echo "\n";
            echo "\t" . self::COMMAND_SKIP_QUERY . ": skip query now, do not mark as executed\n";
            echo "\t" . self::COMMAND_IGNORE_QUERY . ": ignore query - mark as executed, but do not execute (you can execute one manually)\n";
            echo "\tENTER execute query\n";

            $command_str = $this->readStdinAnswer();

            switch ($command_str) {
                case '':
                    $migration_service->executeMigration($query);
                    echo "Query executed.\n";
                    break;
                case self::COMMAND_IGNORE_QUERY:
                    $migration_service->markAsExecutedMigration($query);
                    echo "Query marked as executed without execution.\n";
                    break;

                case self::COMMAND_SKIP_QUERY:
                    echo "Query skipped.\n";
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
        return str_pad('', 60, '_') . "\n\n";
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
