<?php

use Myaf\Core\G;
use Myaf\Core\WebController;

/**
 * Class ErrorController.
 * 框架错误处理.
 */
class ErrorController extends WebController
{
    /**
     * router error.
     */
    public function errorAction(Exception $exception)
    {
        $msg = 'msg: ' . $exception->getMessage() . '<br>';
        $msg .= 'file: ' . $exception->getFile() . '<br>';
        $msg .= 'line: ' . $exception->getLine() . '<br>';
        $msg .= $exception->getTraceAsString();

        switch ($exception->getCode()) {
            case FRAMEWORK_ERR_NOTFOUND_VIEW:
            case FRAMEWORK_ERR_NOTFOUND_MODULE:
            case FRAMEWORK_ERR_NOTFOUND_ACTION:
            case FRAMEWORK_ERR_NOTFOUND_CONTROLLER:
                $this->displayAction(404, $msg);
                break;
            default :
                $this->displayAction(500, $msg);
                break;
        }
    }

    /**
     * display error.
     * @param $code int
     * @param null $message
     */
    public function displayAction($code, $message = null)
    {
        if (G::conf()->common->error->simple) {
            $this->statusCode($code)->shutdown($message, false);
        } else {
            $this->statusCode($code)->display($code, ['message' => $message]);
        }
    }
}