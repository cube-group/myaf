<?php

use Myaf\Core\G;
use Yaf\Dispatcher;
use Yaf\Plugin_Abstract;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;
use Yaf\Route\Supervar;

/**
 * Class RouterPlugin
 * 路由插件
 * <p>支持action符号-的解析</p>
 * <p>支持cli模式下的参数解析</p>
 */
class RouterPlugin extends Plugin_Abstract
{
    public function routerStartup(Request_Abstract $request, Response_Abstract $response)
    {
        parent::routerStartup($request, $response); // TODO: Change the autogenerated stub

        if (!$request->isCli()) {
            $dispatcher = Dispatcher::getInstance();
            //support url route query string params r
            $dispatcher->getRouter()->addRoute("local", new Supervar('r'));
            return;
        }

        //cli模式 php bin/cli module/controller/action value
        $argv = $request->getServer('argv');
        $argc = count($argv);
        if ($argc == 1) {
            G::shutdown("cli argv not enough.\n", false);
        }
        //cli模式
        if ($argc >= 2) {
            $request->setRequestUri($argv[1]);
        }
        //cli模式 支持参数解析
        if ($argc > 2) {
            //set params
            $values = [];
            for ($i = 2; $i < $argc; $i++) {
                $values[] = $argv[$i];
            }
            if ($values) {
                $request->setParam('value', (count($values) == 1) ? $values[0] : $values);
            }
        }
    }

    public function routerShutdown(Request_Abstract $request, Response_Abstract $response)
    {
        parent::routerShutdown($request, $response); // TODO: Change the autogenerated stub

        $uri = "/{$request->getModuleName()}/{$request->getControllerName()}/{$request->getActionName()}";
        G::route(strtolower($uri));
    }

    public function dispatchLoopShutdown(Request_Abstract $request, Response_Abstract $response)
    {
        parent::dispatchLoopShutdown($request, $response); // TODO: Change the autogenerated stub

        //结束前刷新日志
        G::flush();
    }
}