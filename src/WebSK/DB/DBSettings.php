<?php

namespace WebSK\DB;

class DBSettings
{
    protected string $sql_file_path = '';

    protected string $db_connector_id;

    /**
     * DBSettings constructor.
     * @param string $db_connector_id
     * @param string $sql_file_path
     */
    public function __construct(string $db_connector_id, string $sql_file_path = '')
    {
        $this->db_connector_id = $db_connector_id;
        $this->sql_file_path = $sql_file_path;
    }

    /**
     * @return string
     */
    public function getDbConnectorId(): string
    {
        return $this->db_connector_id;
    }

    /**
     * @param string $db_connector_id
     */
    public function setDbConnectorId(string $db_connector_id)
    {
        $this->db_connector_id = $db_connector_id;
    }

    /**
     * @return string
     */
    public function getSqlFilePath(): string
    {
        return $this->sql_file_path;
    }

    /**
     * @param string $sql_file_path
     */
    public function setSqlFilePath(string $sql_file_path)
    {
        $this->sql_file_path = $sql_file_path;
    }
}
