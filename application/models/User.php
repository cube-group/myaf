<?php

use Core\Data;
use Base\Orm\LActiveRecord;

/**
 * Class UserModel.
 */
class UserModel extends LActiveRecord
{
    public function tableName()
    {
        return "users";
    }

    public function database()
    {
        // TODO: Implement database() method.
        return Data::db('default');
    }

    /**
     * 获取用户信息
     * @return array
     */
    public function getUserInfo()
    {
        return ['id' => 1, 'username' => 'test', 'password' => 'xx', 'p1' => 'p1', 'p2' => 'p2'];
    }
}