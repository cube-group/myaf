<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/3/13
 * Time: 上午10:05
 */

namespace Base\Session;

/**
 * Interface ISession.
 * Session标准实现接口.
 * @package Base\Session
 */
interface ISession
{
    /**
     * 设置session过期时间(单位:秒)
     * @param $value
     * @return mixed
     */
    public function setTimeout($value);
    /**
     * 设置session
     * @param $key
     * @param $value
     * @return bool
     */
    public function set($key, $value);

    /**
     * 获取session.
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * 获取cookie中的定义的session key name.
     * @return mixed
     */
    public function getSessionName();

    /**
     * 获取cookie中的sessionName的值
     * 如:PHP_REDIS_SESSION = !@$#$#$^$%^&%$^&$%&$%^
     * @return mixed
     */
    public function getSessionID();

    /**
     * 获取session的过期时间(单位:秒)
     * @return mixed
     */
    public function getTTL();

    /**
     * 重置session的过期时间.
     * @return bool
     */
    public function resetTTL();

    /**
     * 删除session中的某个key.
     * @param $key
     * @return bool
     */
    public function delete($key);

    /**
     * 清除session.
     * @return bool
     */
    public function clear();
}