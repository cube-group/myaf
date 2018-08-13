<?php

use Base\Log\Log;
use Base\Log\LogAction;
use Myaf\Core\G;
use Myaf\Core\WebController;
use Myaf\Utils\ServerUtil;

/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 2018/5/17
 * Time: 下午2:56
 */
class LogController extends WebController
{
    /**
     * 业务类日志测试
     */
    public function indexAction()
    {
        $uid = uniqid();
        Log::debug(G::route(), $uid, 200, 'debug', ['orderSn' => time()]);
        Log::warn(G::route(), $uid, 400, 'warn', ['orderSn' => time()]);
        Log::error(G::route(), $uid, 500, 'debug', ['orderSn' => time()]);
        Log::fatal(G::route(), $uid, 600, 'debug', ['orderSn' => time()]);
        Log::flush();
    }

    /**
     * 统计类日志测试
     */
    public function statAction()
    {
        $uid = uniqid();
        LogAction::init('test', 'v0.0.1');
        LogAction::save('test-action', $uid, ServerUtil::requestIp(), ['key1' => 1, 'key2' => 2]);
        LogAction::flush();
    }
}