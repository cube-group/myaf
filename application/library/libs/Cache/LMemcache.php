<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 16/9/13
 * Time: 下午12:20
 */

namespace libs\Cache;

//extension check.
if (!extension_loaded('memcache')) {
    throw new \Exception('Ext memcache is not exist!');
}

use \Memcache;
use \Exception;

/**
 * Class LMemcache
 * @method set ($key, $var, $flag, $expire)
 * @method get ($key, &$flags)
 * @package libs\Cache
 */
class LMemcache extends LCache
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
     * @var Memcache
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
        try {
            $this->memcache = new Memcache();
            $this->memcache->connect(
                $options["host"],
                $options["port"],
                isset($options['timeout']) ? $options['timeout'] : 3
            );
        } catch (Exception $e) {
            $this->close($e->getMessage());
        }
    }

    public function close($error = '')
    {
        parent::close($error);

        if ($this->memcache) {
            $this->memcache->close();
            $this->memcache = null;
        }
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