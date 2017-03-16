<?php

namespace config;

use lib\MysqlConfiginterface;

class MysqlConfig implements MysqlConfiginterface
{
    /**
     * @var string
     */
    private $host = '127.0.0.1';
    /**
     * @var string
     */
    private $dbname = 'genesis';
    /**
     * @var string
     */
    private $login = 'root';
    /**
     * @var string
     */
    private $password = '';

    /**
     * @inheritdoc
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @inheritdoc
     */
    public function getDbName(): string
    {
        return $this->dbname;
    }

    /**
     * @inheritdoc
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @inheritdoc
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}