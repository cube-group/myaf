<?php

use Core\ControlConsole;
use Core\G;

/**
 * Class TestController
 */
class ConsoleController extends ControlConsole
{
    public function indexAction()
    {
        $this->json(['value' => G::route(), 'param' => $this->getRequest()->getParams()]);
    }
}