<?php

namespace Core;

/**
 * Class ControlWeb.
 * Console Controller基类.
 * (核心类勿改)
 * @package Core
 */
abstract class ControlConsole extends Control
{
    public function init()
    {
        parent::init();

        if (!$this->req->isCli()) {
            $this->shutdown('<b>not cli</b>', false);
        }
    }

    public function send($value)
    {
        echo $value . "\n";
    }

    public function json($data = '', $msg = '', $code = 0)
    {
        echo G::json($data, $msg, $code) . "\n";
    }
}