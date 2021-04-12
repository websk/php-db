<?php

namespace WebSK\DB;

/**
 * Interface DBConnectorInterface
 * @package WebSK\DB
 */
interface DBConnectorInterface
{
    /**
     * @return \PDO
     */
    public function getPdoObj(): \PDO;

    /**
     * @return string
     */
    public function getDbName(): string;
}