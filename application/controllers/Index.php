<?php

use Myaf\Core\G;
use Myaf\Core\WebController;

/**
 * Class IndexController.
 */
class IndexController extends WebController
{
    public function indexAction()
    {
        $this->display('index', ['value' => G::route()]);
    }
}