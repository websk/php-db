<?php

namespace WebSK\DB\Console;

use WebSK\DB\DBService;

/**
 * Class MigrationService
 * @package WebSK\DB\Console
 */
class MigrationService
{
    const string EXECUTED_QUERIES_TABLE_NAME = 'db_executed_queries';

    protected DBService $db_service;

    public function __construct(DBService $db_service)
    {
        $this->db_service = $db_service;
    }

    /**
     * @return string
     */
    public function checkConnectDBAndReturnError(): string
    {
        try {
            $this->db_service->readColumn("SELECT 1");
        } catch (\Exception $e) {
            $output = "Can't connect to database " . $this->db_service->getDbName() . "\n";
            $output .= $e->getMessage() . "\n\n";

            return $output;
        }

        return '';
    }

    /**
     * @param string $query
     * @throws \Exception
     */
    public function markAsExecutedMigration(string $query): void
    {
        $this->db_service->query(
            'INSERT INTO ' . self::EXECUTED_QUERIES_TABLE_NAME . ' (created_at_ts, sql_query) VALUES (?, ?)',
            [time(), $query]
        );
    }

    /**
     * @param string $query
     * @throws \Exception
     */
    public function executeMigration(string $query): void
    {
        if (!$query) {
            return;
        }

        try {
            $this->db_service->query($query);
        } catch (\Throwable $e) {
            $exception_code = $e->getCode();
            throw new \Exception(
                sprintf('Error in: %s', $query),
                is_int($exception_code) ? $exception_code : 0,
                $e
            );
        }

        $this->db_service->query(
            'INSERT INTO ' . self::EXECUTED_QUERIES_TABLE_NAME . ' (created_at_ts, sql_query) VALUES (?, ?)',
            [time(), $query]
        );
    }

    /**
     * @return list<string>
     * @throws \Exception
     */
    public function getExecutedQueriesArr(): array
    {
        return $this->db_service->readColumn(
            'SELECT sql_query FROM ' . self::EXECUTED_QUERIES_TABLE_NAME
        );
    }

    /**
     * @throws \Exception
     */
    public function createMigrationTable(): void
    {
        $this->db_service->query(
            'CREATE TABLE ' . self::EXECUTED_QUERIES_TABLE_NAME
            .' (id int NOT NULL AUTO_INCREMENT PRIMARY KEY, created_at_ts int NOT NULL DEFAULT 0, sql_query text)'
            . ' ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );
    }

    /**
     * @return string
     */
    public function getMigrationFilename(): string
    {
        $db_settings_obj = $this->db_service->getDbSettings();
        $filename = $db_settings_obj->getSqlFilePath();

        return $filename;
    }

    /**
     * @return list<string>
     * @throws \Exception
     */
    public function loadQueriesArrForDB(): array
    {
        $filename = $this->getMigrationFilename();

        if ($filename == '') {
            return [];
        }

        if (!file_exists($filename)) {
            throw new \Exception('Migrations file "' . $filename . '" not found');
        }

        $sql_file_str = file_get_contents($filename);
        if ($sql_file_str === false) {
            throw new \Exception('File "' . $filename . '" read failed.');
        }

        $queries_arr = preg_split('/\R/', $sql_file_str, -1, PREG_SPLIT_NO_EMPTY);
        if ($queries_arr === false) {
            throw new \RuntimeException('Failed to split migrations file content.');
        }

        return $queries_arr;
    }

    /**
     * @param string $query
     * @throws \Exception
     */
    public function addMigrationQuery(string $query): void
    {
        $queries_arr = $this->loadQueriesArrForDB();

        $queries_arr[] = $query;

        $filename = $this->getMigrationFilename();

        $file_contents = implode(PHP_EOL, $queries_arr) . PHP_EOL;
        if (file_put_contents($filename, $file_contents) === false) {
            throw new \Exception('File "' . $filename . '" put failed.');
        }
    }
}
