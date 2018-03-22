<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/3/9
 * Time: 上午11:55
 */

namespace libs\Cache;

/**
 * Class LCache.
 * @package libs\Cache
 */
class LCache implements ICache
{
    /**
     * 最近一次错误.
     * @var string
     */
    protected $lastError = '';

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->close();
    }

    public static function limits()
    {
        // TODO: Implement close() method.
        return [];
    }

    public function configure($options)
    {
        // TODO: Implement close() method.
    }

    public function close($error = '')
    {
        // TODO: Implement close() method.

        $this->lastError = '';
    }
}