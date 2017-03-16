<?php

namespace lib;

/**
 * Interface MysqlConfiginterface
 * @package lib
 */
interface MysqlConfiginterface
{
    /**
     * @return string
     */
    public function getHost(): string;

    /**
     * @return string
     */
    public function getDbName(): string;

    /**
     * @return string
     */
    public function getLogin(): string;

    /**
     * @return string
     */
    public function getPassword(): string;
}