<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 2017/11/17
 * Time: 下午5:47
 */

namespace Core;

use Base\Mongo\LMongo;
use Exception;
use Base\Cache\LMemcache;
use Base\Cache\LRedis;
use Base\Orm\LDB;

/**
 * mysql、mongodb、redis、memcache连接实例
 * Class Data
 * @package Core
 */
class Data
{
    /**
     * 连接队列
     * @var array
     */
    private static $connections = [];

    /**
     * 获取数据库连接操作实例LDB
     * @param $name string
     * @return bool|LDB
     * @throws Exception
     */
    public static function db($name = 'default')
    {
        $conf = G::conf();
        if (!$conf->mysql || !$conf->mysql->$name) {
            throw new Exception("mysql {$name} connection config is null");
        }
        if (!$conf->mysql->$name->master) {
            throw new Exception("mysql {$name} connection master config is null");
        }
        $key = 'mysql-' . $name;
        if (!isset(self::$connections[$key])) {
            self::$connections[$key] = new LDB(
                $conf->mysql->$name->master->toArray(),
                $conf->mysql->$name->slave ? $conf->mysql->$name->slave->toArray() : null
            );
        }
        return self::$connections[$key];
    }

    /**
     * 获取Mongo操作实例
     * @param $name string
     * @return bool|LMongo
     * @throws Exception
     */
    public static function mongo($name = 'default')
    {
        $conf = G::conf();
        if (!$conf->mongo || !$conf->mongo->$name) {
            throw new Exception("mongo {$name} connection config is null");
        }
        $key = 'mongo-' . $name;
        if (!isset(self::$connections[$key])) {
            self::$connections[$key] = new LMongo($conf->mongo->$name->toArray());
        }
        return self::$connections[$key];
    }

    /**
     * 获取Redis连接操作实例LRedis
     * @param $name string
     * @return bool|LRedis
     * @throws Exception
     */
    public static function redis($name = 'default')
    {
        $conf = G::conf();
        if (!$conf->redis || !$conf->redis->$name) {
            throw new Exception("redis {$name} connection config is null");
        }
        $key = 'redis-' . $name;
        if (!isset(self::$connections[$key])) {
            self::$connections[$key] = new LRedis($conf->redis->$name->toArray());
        }
        return self::$connections[$key];
    }

    /**
     * 获取Redis连接操作实例LMemcache
     * @param $name string
     * @return bool|LMemcache
     * @throws Exception
     */
    public static function cache($name = 'default')
    {
        $conf = G::conf();
        if (!$conf->memcache || !$conf->memcache->$name) {
            throw new Exception("redis {$name} connection config is null");
        }
        $key = 'memcache-' . $name;
        if (!isset(self::$connections[$key])) {
            self::$connections[$key] = new LMemcache($conf->memcache->$name->toArray());
        }
        return self::$connections[$key];
    }
}