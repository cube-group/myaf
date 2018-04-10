<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 16/9/13
 * Time: 下午12:20
 */

namespace Base\Cache;

use Memcached;

/**
 * Class LMemcache
 * @method set ($key, $var, $expire = 0)
 * @method get ($key)
 * @package Base\Cache
 */
class LMemcache implements ICache
{
    /**
     * 限制函数名
     * @return array
     */
    public static function limits()
    {
        return [
            'pconnect', 'addServer', 'close', 'connect', 'flush', 'setServerParams', 'getExtendedStats'
        ];
    }

    /**
     * @var Memcached
     */
    protected $memcache;


    /**
     * LMemcache constructor.
     * @param array $options
     * array(
     *      'host'=>'localhost',
     *      'port'=>4396
     * );
     */
    public function __construct($options = [])
    {
        if ($options) {
            $this->configure($options);
        }
    }

    public function close($error = '')
    {
        // TODO: Implement close() method.
        if($this->memcache){
            $this->close();
            $this->memcache = null;
        }
    }

    /**
     * DataStore constructor.
     * @param $options array
     * array(
     *      'host'=>'localhost',
     *      'port'=>4396
     * );
     */
    public function configure($options)
    {
        $this->memcache = new Memcached();
        $this->memcache->addServer(
            $options["host"],
            $options["port"]
        );
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public function __call($name, $arguments)
    {
        if ($this->memcache) {
            if (method_exists($this->memcache, $name) && !in_array($name, self::limits())) {
                return call_user_func_array([$this->memcache, $name], $arguments);
            }
        }
        return false;
    }
}