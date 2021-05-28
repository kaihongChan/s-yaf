<?php

namespace db;

class Mysql
{
    /**
     * @var Mysql
     */
    private static $instance;

    private $conf;

    private $connection;

    /**
     * 获取Mysql实例
     * @param $conf
     * @return Mysql
     */
    public function getInstance($conf): Mysql
    {
        if (empty(self::$instance) || !(self::$instance instanceof Mysql)) {
            self::$instance = new self($conf);
        }

        return self::$instance;
    }

    /**
     * Mysql constructor.
     */
    private function __construct($conf)
    {
//        $this->connection = new \PDO();
    }
}