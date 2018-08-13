<?php

use Myaf\Core\WebController;

/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 2018/8/13
 * Time: 上午11:59
 */
class ErrController extends WebController
{
    public function indexAction()
    {
        throw new Exception('test');
    }
}