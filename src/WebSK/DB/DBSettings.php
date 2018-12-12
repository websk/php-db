<?php

namespace WebSK\DB;

/**
 * Class DBSettings
 * @package VitrinaTV\Core\DB
 */
class DBSettings
{
    /** @var string */
    protected $sql_file_path = '';
    /** @var string */
    protected $db_connector_id;

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
    public function getDbConnectorId()
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
    public function getSqlFilePath()
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
