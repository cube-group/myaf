<?php

use Myaf\Core\ConsoleController;
use Myaf\Core\G;

/**
 * Class TestController
 */
class CmdController extends ConsoleController
{
    public function indexAction()
    {
        $this->json(['value' => G::route(), 'params' => $this->params()]);
    }
}