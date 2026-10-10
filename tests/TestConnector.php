<?php

namespace WebSK\DB\Tests;

use PDO;
use WebSK\DB\DBConnectorInterface;

class TestConnector implements DBConnectorInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $db_name = 'test'
    ) {
    }

    public function getPdoObj(): PDO
    {
        return $this->pdo;
    }

    public function getDbName(): string
    {
        return $this->db_name;
    }
}
