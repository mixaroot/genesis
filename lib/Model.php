<?php

namespace lib;

/**
 * Class Model
 * @package lib
 */
abstract class Model
{
    /**
     * @var \lib\MysqlConfiginterface|null
     */
    protected $oConfig = null;
    /**
     * @var null
     */
    private $oConnect = null;

    /**
     * @param \lib\MysqlConfiginterface $oConfig
     */
    public function __construct(MysqlConfiginterface $oConfig)
    {
        $this->oConfig = $oConfig;
    }

    /**
     * @return null|\PDO
     */
    protected function getConnect()
    {
        if (null === $this->oConnect) {
            $host = $this->oConfig->getHost();
            $dbname = $this->oConfig->getDbName();
            $dsn = "mysql:host=$host;dbname=$dbname";
            $username = $this->oConfig->getLogin();
            $passwd = $this->oConfig->getPassword();
            $this->oConnect = new \PDO($dsn, $username, $passwd);
        }
        return $this->oConnect;
    }
}