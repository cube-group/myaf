<?php

use Myaf\Core\G;
use Myaf\Core\WebController;
use Myaf\Log\Log;

/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 2018/5/17
 * Time: 下午2:56
 */
class LogController extends WebController
{
    public function indexAction()
    {
        $uid = uniqid();
        Log::debug(G::route(), $uid, 200, 'debug', ['orderSn' => time()], ['time' => time()], ['version' => PHP_VERSION]);
        Log::warn(G::route(), $uid, 400, 'warn', ['orderSn' => time()], ['time' => time()], ['version' => PHP_VERSION]);
        Log::error(G::route(), $uid, 500, 'debug', ['orderSn' => time()], ['time' => time()], ['version' => PHP_VERSION]);
        Log::fatal(G::route(), $uid, 600, 'debug', ['orderSn' => time()], ['time' => time()], ['version' => PHP_VERSION]);
    }
}