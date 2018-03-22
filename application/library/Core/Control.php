<?php

namespace Core;

use Yaf\Controller_Abstract;
use Yaf\Request\Http;

/**
 * Class Control
 * Controller基类
 * @package Core
 */
abstract class Control extends Controller_Abstract
{
    /**
     * @var Http
     */
    protected $req;

    public function init()
    {
        $this->req = $this->_request;
    }

    /**
     * 向缓冲区发送字符串
     * @param $value string|array
     */
    public function send($value)
    {
        if ($value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            echo $value;
        }
        echo '';
    }

    /**
     * 以json形式标准接口输出.
     * @param string|array $data
     * @param $msg string
     * @param int $code
     */
    public function json($data = '', $msg = '', $code = 0)
    {
        echo G::json($data, $msg, $code);
    }

    /**
     * 终止路由的执行并返回相关信息
     * @param $msg string
     * @param bool $json
     */
    public function shutdown($msg, $json = true)
    {
        G::shutdown($msg, $json);
    }

    /**
     * 是否为cli模式
     * @return bool
     */
    public function isCli()
    {
        return $this->getRequest()->isCli();
    }


    /**
     * 是否为HTTP HEAD
     * @return bool
     */
    public function isHead()
    {
        return $this->getRequest()->isHead();
    }

    /**
     * 是否为HTTP POST
     * @return bool
     */
    public function isPost()
    {
        return $this->getRequest()->isPost();
    }

    /**
     * 是否为HTTP GET
     * @return bool
     */
    public function isGet()
    {
        return $this->getRequest()->isGet();
    }

    /**
     * 是否为HTTP PUT
     * @return bool
     */
    public function isPut()
    {
        return $this->getRequest()->isPut();
    }

    /**
     * 是否为ajax访问.
     * @return bool
     */
    public function isAjax()
    {
        return $this->getRequest()->isXmlHttpRequest();
    }
}