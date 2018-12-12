<?php

namespace WebSK\DB;


interface DBConnectorInterface
{
    public function getPdoObj();
    public function getDbName();
}