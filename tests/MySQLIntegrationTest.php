<?php

namespace WebSK\DB\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use WebSK\DB\Console\MigrationService;
use WebSK\DB\DBConnectorMySQL;
use WebSK\DB\DBService;
use WebSK\DB\DBSettings;

#[Group('mysql')]
class MySQLIntegrationTest extends TestCase
{
    public function testConnectorAndMigrationTableOnMySql(): void
    {
        $host = getenv('MYSQL_TEST_HOST');
        $database = getenv('MYSQL_TEST_DATABASE');
        $user = getenv('MYSQL_TEST_USER');
        $password = getenv('MYSQL_TEST_PASSWORD');

        if ($host === false || $database === false || $user === false || $password === false) {
            self::markTestSkipped('MySQL test environment is not configured.');
        }

        $db_service = new DBService(
            new DBConnectorMySQL($host, $database, $user, $password),
            new DBSettings('mysql')
        );
        $migration_service = new MigrationService($db_service);

        $db_service->query('DROP TABLE IF EXISTS db_executed_queries');

        try {
            $migration_service->createMigrationTable();
            $migration_service->markAsExecutedMigration('SELECT 1');

            self::assertSame(['SELECT 1'], $migration_service->getExecutedQueriesArr());
            self::assertSame('', $migration_service->checkConnectDBAndReturnError());
        } finally {
            $db_service->query('DROP TABLE IF EXISTS db_executed_queries');
        }
    }
}
