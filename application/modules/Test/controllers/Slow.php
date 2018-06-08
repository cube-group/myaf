<?php

use Myaf\Core\WebController;

/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 2018/6/8
 * Time: 上午10:47
 */
class SlowController extends WebController
{
    public function indexAction()
    {
        sleep(3);
        echo "模拟php-fpm slow log";
    }
}