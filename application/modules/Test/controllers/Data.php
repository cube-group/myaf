<?php

use Myaf\Core\WebController;
use Myaf\Pool\Data;

/**
 * Class DataController
 * 数据相关处理路由
 */
class DataController extends WebController
{
    /**
     * override init
     */
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function indexAction()
    {
        $model = new UserModel();
        var_dump($model->find()->one(['id' => 1]));
    }

    public function mysqlAction()
    {
        $select = Data::db('default')->table('users');
        var_dump($select->select('id'));
    }

    public function memAction()
    {
        Data::cache('default')->set('test-key', time());
        var_dump(Data::cache('default')->get('test-key'));
    }

    public function redisAction()
    {
        Data::redis('default')->set('test-key', time());
        var_dump(Data::redis('default')->get('test-key'));
    }

    public function mongoAction()
    {
        $rt = Data::mongo()->model('collect')->insertOne(['a' => 0]);
        var_dump($rt);
        $rt = Data::mongo()->model('collect')->findOne(['a' => 0]);
        var_dump($rt);
    }

    public function infoAction()
    {
        var_dump((new UserModel())->getUserInfo());
    }
}