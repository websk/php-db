<?php

namespace Websk\DB;


interface DBConnectorInterface
{
    public function getPdoObj();
    public function getDbName();
}