<?php

use Core\D;
use Core\G;
use Yaf\Bootstrap_Abstract;
use Yaf\Dispatcher;

/**
 * Class Bootstrap.
 * Framework Facade.
 */
class Bootstrap extends Bootstrap_Abstract
{
    /**
     * 常规配置.
     * @param Dispatcher $dispatcher
     */
    public function _initCommon(Dispatcher $dispatcher)
    {
        $dispatcher->disableView();
        $dispatcher->autoRender(false);

        //初始化全局配置
        G::init($dispatcher->getApplication()->getConfig());
    }

    /**
     * 设置错误等级.
     * @param Dispatcher $dispatcher
     */
    public function _initError(Dispatcher $dispatcher)
    {
        if (G::conf()->common->error->report !== null) {
            error_reporting(G::conf()->common->error->report);
        }
        if (G::conf()->common->error->display !== null) {
            ini_set('display_errors', G::conf()->common->error->display);
        }
    }

    /**
     * initialize plugins
     * (务必注意插件的注册顺序).
     * @param Dispatcher $dispatcher
     */
    public function _initPlugins(Dispatcher $dispatcher)
    {
        $dispatcher->registerPlugin(new RouterPlugin());
        $dispatcher->registerPlugin(new StaticPlugin());
    }
}