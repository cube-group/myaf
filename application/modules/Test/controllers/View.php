<?php

use Myaf\Core\G;

/**
 * Class IndexController
 */
class ViewController extends \Myaf\Core\WebController
{
    public function indexAction()
    {
        $this->display('index', ['value' => G::route()]);
    }
}