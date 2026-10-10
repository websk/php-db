<?php

namespace WebSK\DB\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use WebSK\DB\Console\MigrationService;
use WebSK\DB\DBService;
use WebSK\DB\DBSettings;

class MigrationServiceTest extends TestCase
{
    private string $migration_file;

    private DBService $db_service;

    protected function setUp(): void
    {
        $migration_file = tempnam(sys_get_temp_dir(), 'php-db-migrations-');
        if ($migration_file === false) {
            self::fail('Failed to create a temporary migrations file.');
        }

        $this->migration_file = $migration_file;

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db_service = new DBService(
            new TestConnector($pdo, 'sqlite-memory'),
            new DBSettings('sqlite', $this->migration_file)
        );
    }

    protected function tearDown(): void
    {
        if (is_file($this->migration_file)) {
            unlink($this->migration_file);
        }
    }

    public function testLoadsOneQueryPerNonEmptyLine(): void
    {
        file_put_contents($this->migration_file, "SELECT 1\n\nSELECT 2\r\n");

        $service = new MigrationService($this->db_service);

        self::assertSame(['SELECT 1', 'SELECT 2'], $service->loadQueriesArrForDB());
    }

    public function testAddsQueryUsingTheSameLineBasedFormat(): void
    {
        file_put_contents($this->migration_file, "SELECT 1\n");
        $service = new MigrationService($this->db_service);

        $service->addMigrationQuery('SELECT 2');

        self::assertSame("SELECT 1\nSELECT 2\n", file_get_contents($this->migration_file));
        self::assertSame(['SELECT 1', 'SELECT 2'], $service->loadQueriesArrForDB());
    }

    public function testEmptyPathHasNoQueries(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $service = new MigrationService(
            new DBService(new TestConnector($pdo), new DBSettings('sqlite'))
        );

        self::assertSame([], $service->loadQueriesArrForDB());
    }

    public function testMissingFileThrowsException(): void
    {
        unlink($this->migration_file);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not found');
        (new MigrationService($this->db_service))->loadQueriesArrForDB();
    }

    public function testExecutesAndRecordsMigrations(): void
    {
        $this->db_service->query(
            'CREATE TABLE db_executed_queries ('
            . 'id INTEGER PRIMARY KEY AUTOINCREMENT, created_at_ts INTEGER NOT NULL, sql_query TEXT)'
        );
        $service = new MigrationService($this->db_service);
        $query = 'CREATE TABLE migrated (id INTEGER PRIMARY KEY)';

        $service->executeMigration($query);
        $service->markAsExecutedMigration('SELECT 1');

        self::assertSame([$query, 'SELECT 1'], $service->getExecutedQueriesArr());
        self::assertSame('migrated', $this->db_service->readField(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'migrated'"
        ));
    }

    public function testMigrationFailurePreservesPreviousException(): void
    {
        $service = new MigrationService($this->db_service);

        try {
            $service->executeMigration('INVALID SQL');
            self::fail('Invalid migration must fail.');
        } catch (\Exception $exception) {
            self::assertNotNull($exception->getPrevious());
            self::assertStringContainsString('INVALID SQL', $exception->getMessage());
        }
    }

    public function testConnectionCheckReturnsAnEmptyStringForWorkingConnection(): void
    {
        self::assertSame('', (new MigrationService($this->db_service))->checkConnectDBAndReturnError());
    }
}
