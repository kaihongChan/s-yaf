<?php

namespace Application\Library\Cache;

final class Redis
{
    /**
     * @var Redis
     */
    private static $instance;

    private $host = '127.0.0.1';

    private $port = '6379';

    public $client;

    /**
     * 获取Redis实例
     * @return Redis
     */
    public static function getInstance(): Redis
    {
        if (empty(self::$instance) || !(self::$instance instanceof Redis)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {

    }


}