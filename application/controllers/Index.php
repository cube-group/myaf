<?php

use Core\ControlWeb;
use Core\G;

/**
 * Class IndexController.
 */
class IndexController extends ControlWeb
{
    public function indexAction()
    {
        $this->display('index', ['value' => G::route()]);
    }
}