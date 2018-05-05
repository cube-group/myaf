<?php
use Myaf\Core\G;

/**
 * Class IndexController
 * Restful Mode
 */
class RestController extends \Myaf\Core\RestController
{
    public function _404Action()
    {
        G::code(404);
        $this->response(false, ['type' => $this->_request->method], 404);
    }

    public function GET_usersAction()
    {
        $users = new UserModel();
        $result = $users->find()->asArray()->select();
        $this->response(true, ['type' => $this->_request->method, 'sql' => $users->getDb()->lastSql(), 'list' => $result], 200);
    }

    public function POST_usersAction()
    {
        $users = new UserModel();
        $users->name = time();
        $result = $users->save();
        $this->response(true, ['type' => $this->_request->method, 'result' => $result], 200);
    }

    public function PUT_usersAction()
    {
        $users = new UserModel();
        $result = $users->insert(['name' => time()]);
        $this->response(true, ['type' => $this->_request->method, 'result' => $result], 200);
    }

    public function DELETE_usersAction()
    {
        $this->response(true, ['type' => $this->_request->method], 200);
    }
}