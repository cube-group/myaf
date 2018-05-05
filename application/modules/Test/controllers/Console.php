<?php
use Myaf\Core\G;

/**
 * Class TestController
 */
class ConsoleController extends \Myaf\Core\ConsoleController
{
    public function indexAction()
    {
        $this->json(['value' => G::route(), 'param' => $this->getRequest()->getParams()]);
    }
}