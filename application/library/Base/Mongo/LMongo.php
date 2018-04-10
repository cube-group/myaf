<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 16/9/13
 * Time: 下午12:20
 */
namespace Base\Mongo;

//extension check.
use MongoClient;

if (!extension_loaded('mongo')) {
    throw new \Exception('Ext mongo is not exist!');
}

/**
 * Class LMongo
 * @package libs\Mongo
 */
class LMongo
{
    /**
     * Mongo connect instance.
     * @var MongoClient
     */
    private $mongo;

    /**
     * close the mongodb connection.
     * @return bool
     */
    public function close()
    {
        if (!$this->mongo) {
            return true;
        }
        try {
            $connections = $this->mongo->getConnections();
            foreach ($connections as $con) {
                if ($con['connection']['connection_type_desc'] == "SECONDARY") {
                    $this->mongo->close($con['hash']);
                }
            }
            $this->mongo = null;
            return true;
        } catch (\MongoException $e) {
        } catch (\ErrorException $e) {
        } catch (\Exception $e) {
        }
        return false;
    }

    /**
     * get the Mongo collection.
     *
     * $mongo->model('list')->find();
     * SQL:select * from list;
     * $mongo->model('list')->find(array('name'=>'hello'));
     * SQL:select * from list where name="hello";
     * $mongo->model('list')->find(array('name'=>'hello'),array('name','group'));
     * SQL:select name,group from list where name="hello";
     * $mongo->model('list')->find(array('$or'=>array('a'=>1,'b'=>2));
     * SQL:select * from list where (a=1 or b=2);
     * $mongo->model('list')->find(array('$and'=>array('a'=>1,'b'=>2));
     * SQL:select * from list where (a=1 and b=2);
     * $mongo->model('list')->find(array('$or'=>array('a'=>1,'b'=>2,'$and'=>array('c'=>3,'d'=>4)));
     * SQL:select * from list where (a=1 or b=2 or (c=3 and d=4));
     * $mongo->model('list')->find(array('$gt'=>array('c'=>4)));
     * SQL:select * from list where c>4;
     * $mongo->model('list')->find(array('$gte'=>array('c'=>4)));
     * SQL:select * from list where c>=4;
     * $mongo->model('list')->find(array('$lt'=>array('c'=>4)));
     * SQL:select * from list where c<4;
     * $mongo->model('list')->find(array('$lte'=>array('c'=>4)));
     * SQL:select * from list where c<=4;
     *
     * $mongo->model('list')->findOne(array('name'=>'hello'));
     * SQL:select * from list limit 0,1;
     *
     * $mongo->:model('list')->find()->sort(array('name'=>1));
     * SQL:select * from list order by name asc;
     * $mongo->model('list')->find()->sort(array('name'=>-1));
     * SQL:select * from list order by name desc;
     *
     * $mongo->model('list')->find()->skip(0)->limit(10);
     * SQL:select * from list limit 0,10;
     *
     * $mongo->model('list')->update(array('name'=>'hello'),array('$set'=>array('a'=>1,'b'=>2));
     * SQL:update list a=1,b=2 where name="hello";
     *
     * $mongo->model('list')->update(array('name'=>'hello'),array('$inc'=>array('a'=>1));
     * SQL:update list a=a+1 where name="hello";
     *
     * ...$filter: $or/$and/$gt/$gte/$lt/$lte...
     * ...more $ you need to google...$set/$inc/$unset/$push/$pop/$upsert...
     *
     * $mongo->model('list')->remove({'name'=>'hello'});
     * SQL:delete from list where name="hello";
     *
     * $mongo->model('list')->insert({'name'=>'hello'});
     * SQL:insert into list name values 'hello';
     *
     * $mongo->model('list')->insert({'name'=>'hello'});
     * SQL:insert into list name values 'hello';
     *
     * $mongo->model('list')->save({'name'=>'hello'});
     * SQL:INSERT INTO list (name) SELECT ('hello') FROM VISUAL WHERE NOT EXISTS (SELECT * FROM list WHERE name="hello");
     *
     * @param $options array
     * @return mixed
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->connect($options);
        }
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->close();
    }

    /**
     * 进行数据库正式连接.
     * @param $options array
     * @return $this
     */
    public function connect($options)
    {
        $this->close();
        try {
            $this->mongo = new MongoClient(
                'mongodb://' . $options['host'] . ':' . $options['port'],
                [
                    'username' => $options['username'],
                    'password' => $options['password'],
                    'db' => $options['database']
                ]
            );
        } catch (\Exception $e) {
            $this->mongo = null;
        }
        return $this;
    }


    /**
     * 获取Mongo的Collection操作实例.
     * @param $collectionName
     * @return bool|\MongoDB
     */
    public function model($collectionName)
    {
        if ($this->mongo) {
            return $this->mongo->$collectionName;
        }
        return false;
    }

    /**
     * 以工厂模式创建Mongo实例
     * @param $options array
     * @return MongoClient|bool
     */
    public static function create($options)
    {
        $m = new LMongo($options);
        if ($m->mongo) {
            return $m;
        }
        return false;
    }
}
