<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/3/16
 * Time: 下午4:13
 */

namespace Core;

/**
 * Class R
 * (核心类勿改)
 * Registry全局参数值存储器key枚举.
 * 详见README-Registry.md
 * @package Core
 */
final class R
{
    /**
     * 框架启动时间(毫秒时间戳).
     */
    const RUNTIME_TIME = 'runtimeTime';
    /**
     * 全局配置.
     */
    const INI_CONF = 'config';
    /**
     * 虚拟route(module/controller/action).
     */
    const VALUE_ROUTE = 'route';
    /**
     * 用户的session_id.
     */
    const VALUE_USER_ID = 'userId';
    /**
     * 用户的userInfo.
     */
    const VALUE_USER_INFO = 'userInfo';
    /**
     * 最近一次的message内容.
     */
    const VALUE_MSG = 'msg';
    /**
     * 最近一次的错误码内容.
     */
    const VALUE_CODE = 'code';
}