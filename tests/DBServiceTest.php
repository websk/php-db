<?php

namespace WebSK\DB\Tests;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use WebSK\DB\DB;
use WebSK\DB\DBService;
use WebSK\DB\DBServiceFactory;
use WebSK\DB\DBSettings;
use WebSK\DB\DBWrapper;

class DBServiceTest extends TestCase
{
    private PDO $pdo;

    private DBService $service;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->service = new DBService(
            new TestConnector($this->pdo, 'sqlite-memory'),
            new DBSettings('sqlite')
        );

        $this->service->query(
            'CREATE TABLE sample ('
            . 'id INTEGER PRIMARY KEY AUTOINCREMENT, '
            . 'name TEXT, active INTEGER, score REAL, note TEXT NULL)'
        );
    }

    public function testQueryBindsSupportedPositionalParameterTypes(): void
    {
        $this->service->query(
            'INSERT INTO sample (name, active, score, note) VALUES (?, ?, ?, ?)',
            ['alpha', true, 1.5, null]
        );

        self::assertSame('1', $this->service->lastInsertId());
        self::assertSame(
            [
                'name' => 'alpha',
                'active' => 1,
                'score' => 1.5,
                'note' => null,
            ],
            $this->service->readAssocRow('SELECT name, active, score, note FROM sample')
        );
    }

    public function testQueryBindsNamedParameters(): void
    {
        $this->service->query(
            'INSERT INTO sample (name, active, score) VALUES (:name, :active, :score)',
            [':name' => 'named', ':active' => false, ':score' => 2.25]
        );

        self::assertSame('named', $this->service->readField('SELECT name FROM sample'));
    }

    public function testReadMethodsReturnExpectedShapes(): void
    {
        $this->service->query(
            'INSERT INTO sample (name, active, score) VALUES (?, ?, ?), (?, ?, ?)',
            ['alpha', true, 1.5, 'beta', false, 2.5]
        );

        self::assertSame(['alpha', 'beta'], $this->service->readColumn('SELECT name FROM sample ORDER BY id'));
        self::assertCount(2, $this->service->readAssoc('SELECT * FROM sample ORDER BY id'));
        self::assertSame('alpha', $this->service->readObject('SELECT name FROM sample WHERE id = 1')->name);

        $objects = $this->service->readObjects('SELECT id, name FROM sample ORDER BY id', [], 'id');
        self::assertArrayHasKey(1, $objects);
        self::assertSame('beta', $objects[2]->name);

        self::assertFalse($this->service->readField('SELECT name FROM sample WHERE id = 999'));
        self::assertFalse($this->service->readAssocRow('SELECT name FROM sample WHERE id = 999'));
        self::assertFalse($this->service->readObject('SELECT name FROM sample WHERE id = 999'));
    }

    public function testQueryRejectsObjectsAndUnsupportedArrayValues(): void
    {
        try {
            $this->service->query('SELECT ?', [new \stdClass()]);
            self::fail('Object parameter must be rejected.');
        } catch (\Exception $exception) {
            self::assertSame('0 passed object', $exception->getMessage());
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('unknown param type');
        $this->service->query('SELECT ?', [[]]);
    }

    public function testTransactionsAreDelegatedToPdo(): void
    {
        self::assertTrue($this->service->beginTransaction());
        self::assertTrue($this->service->inTransaction());
        self::assertTrue($this->service->rollBackTransaction());
        self::assertFalse($this->service->inTransaction());

        self::assertTrue($this->service->beginTransaction());
        self::assertTrue($this->service->commitTransaction());
    }

    public function testDbAlwaysReturnsItsPdoInstance(): void
    {
        $db = new DB(new TestConnector($this->pdo));

        self::assertSame($this->pdo, $db->getPdoObj());
    }

    public function testQueryPreservesPreviousPdoException(): void
    {
        try {
            $this->service->query('SELECT * FROM missing_table');
            self::fail('Invalid SQL must fail.');
        } catch (PDOException $exception) {
            self::assertInstanceOf(PDOException::class, $exception->getPrevious());
            self::assertStringContainsString('missing_table', $exception->getMessage());
        }
    }

    public function testWrapperDelegatesToConfiguredService(): void
    {
        DBWrapper::setDbService($this->service);
        $this->service->query('INSERT INTO sample (name) VALUES (?)', ['wrapped']);

        self::assertSame('wrapped', DBWrapper::readField('SELECT name FROM sample'));
        self::assertSame('1', DBWrapper::lastInsertId());
    }

    public function testFactoryRejectsIncompleteMySqlConfiguration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing database config key: password');

        DBServiceFactory::factoryMySQL([
            'host' => 'localhost',
            'db_name' => 'test',
            'user' => 'test',
        ]);
    }
}
