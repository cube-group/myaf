<?php

namespace libs\Session;

use Exception;
use Base\Cache\LRedis;
use Base\Utils\Arrays;

/**
 * Class LRedisSession
 * @package Session
 */
class LRedisSession extends Session
{
    /**
     * @var LRedis
     */
    private $redis;
    /**
     * @var array
     */
    private $options;

    /**
     * LRedisSession constructor.
     * @param $options array
     * @throws Exception
     */
    public function __construct($options = null)
    {
        $this->sessionName = 'REDIS_PHPSESSID';
        $this->options = $options;

        if (!$this->sessionId = Arrays::get($_COOKIE, $this->sessionName, '')) {
            $this->sessionId = 'ssid-' . md5(uniqid() . time());
            setcookie($this->sessionName, $this->sessionId, time() + $this->sessionTimeout, '/');
        }
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->options = null;
        if ($this->redis) {
            $this->redis->close();
            $this->redis = null;
        }
    }

    protected function redis()
    {
        if (!$this->redis) {
            $this->redis = new LRedis($this->options);
        }
        return $this->redis;
    }

    /**
     * 设置超时时间
     * @param $value
     * @return $this
     */
    public function setTimeout($value)
    {
        $this->sessionTimeout = $value;
        return $this;
    }

    public function set($name, $value)
    {
        if (!$name) {
            return false;
        }
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }
        if ($this->sessionId) {
            if ($this->redis()->hSet($this->sessionId, $name, $value)) {
                $this->resetTTL();//如果新加的key则进行设置timeout
            }
            return true;
        }
        return false;
    }

    public function get($name)
    {
        if ($this->sessionId) {
            $rt = $this->redis()->hGet($this->sessionId, $name);
            try {
                if ($sRt = unserialize($rt)) {
                    return $sRt;
                }
            } catch (Exception $e) {
            }
            return $rt;
        }
        return false;
    }

    public function getSessionName()
    {
        return $this->sessionName;
    }

    public function getSessionID()
    {
        return $this->sessionId;
    }

    public function resetTTL()
    {
        if ($this->sessionId) {
            @setcookie($this->sessionName, $this->sessionId, time() + $this->sessionTimeout, '/');
            return $this->redis()->expire($this->sessionId, $this->sessionTimeout);
        }
        return false;
    }

    public function getTTL()
    {
        if ($this->sessionId) {
            return $this->redis()->ttl($this->sessionId);
        }
        return false;
    }

    public function delete($key)
    {
        return $this->redis()->hDel($this->sessionId, $key);
    }

    public function clear()
    {
        @setcookie($this->sessionName, '', time() - 3600, '/');
        return $this->redis()->del($this->sessionId);
    }
}