<?php

namespace WebSK\DB;

/**
 * Interface DBConnectorInterface
 * @package WebSK\DB
 */
interface DBConnectorInterface
{
    public function getPdoObj();

    public function getDbName();
}