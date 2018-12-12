<?php

namespace WebSK\DB;

class DBConnectorMySQL implements DBConnectorInterface
{
    /** @var string */
    protected $server_host;
    /** @var string */
    protected $db_name;
    /** @var string */
    protected $user;
    /** @var string */
    protected $password;
    /** @var \PDO */
    protected $pdo_obj = null;
    /** @var bool */
    protected $pdo_is_connected = false;

    /**
     * DBConnectorMySQL constructor.
     * @param string $server_host
     * @param string $db_name
     * @param string $user
     * @param string $password
     */
    public function __construct(string $server_host, string $db_name, string $user, string $password)
    {
        $this->server_host = $server_host;
        $this->db_name = $db_name;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getDbName()
    {
        return $this->db_name;
    }

    /**
     * Подключается к серверу при первом обращении за объектом PDO.
     * @return \PDO
     */
    public function getPdoObj()
    {
        if ($this->pdo_is_connected) {
            return $this->pdo_obj;
        }

        $pdo_obj = new \PDO('mysql:host=' . $this->server_host . ';dbname=' . $this->db_name . ';charset=utf8', $this->user, $this->password);
        $pdo_obj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo_obj->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        $this->pdo_obj = $pdo_obj;
        $this->pdo_is_connected = true;

        return $this->pdo_obj;
    }
}