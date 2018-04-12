<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 16/9/12
 * Time: 下午1:47
 */

namespace Base\Session;

/**
 * Class LSession
 * 强烈建议您放弃LSession
 * 强烈建议放弃PHP Session
 * @package Base\Session
 */
class LSession extends Session
{
    /**
     * LSession constructor.
     * @param int $sessionTimeout
     */
    public function __construct($sessionTimeout = 86400)
    {
        @session_start();

        $this->sessionTimeout = $sessionTimeout;
    }

    public function __destruct()
    {
        @session_write_close();
    }

    public function setTimeout($value)
    {
        $this->sessionTimeout = $value;
        return $this;
    }

    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
        return true;
    }

    public function get($name)
    {
        if (!isset($_SESSION[$name])) {
            return false;
        }
        return $_SESSION[$name];
    }

    public function getSessionName()
    {
        return @session_name();
    }

    public function getSessionID()
    {
        return @session_id();
    }

    public function resetTTL()
    {
        return @setcookie(session_name(), session_id(), time() + $this->sessionTimeout, '/');
    }

    public function getTTL()
    {
        return -1;
    }

    public function delete($options)
    {
        unset($_SESSION[$options]);
        return true;
    }

    public function clear()
    {
        if (isset($_COOKIE[session_name()])) {
            @setcookie(session_name(), '', time() - 42000, '/');
        }
        @session_unset();
        @session_destroy();
        return true;
    }
}