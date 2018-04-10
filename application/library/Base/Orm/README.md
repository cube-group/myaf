## LDB是对pdo_mysql扩展(\PDO类)的高级封装
### 单库Demo
```
$db = LDB::create([
    'host' => '127.0.0.1',
    'port' => 3306,
    'username' => 'root',
    'password' => '*****',
    'database' => 'task',
    'prefix' => ''
]);

$result = $db->table('list')->select();
var_dump($result);
$this->printSQL($db->lastSql(), $db->lastError());
```
### 主从库Demo
```
$options = [
   'host' => '127.0.0.1',
   'port' => 3306,
   'username' => 'root',
   'password' => '*****',
   'database' => 'task',
   'prefix' => ''
];
$optionsSlave = [
   'host' => '127.0.0.1',
   'port' => 3307,
   'username' => 'root',
   'password' => '*****',
   'database' => 'task',
   'prefix' => ''
];
$db = LDB::create($options,$optionsSlave);

//使用了从库
$result = $db->table('list')->select();

//使用了主库
$result = $db->table('list')->where('a=1')->delete();
```
### LDB核心方法
* create - (静态方法)工厂模式生成Mysql连接
* table - 获取LDBModel实例
* exec - 执行sql用于insert、update、delete
* query - 执行sql用于select
* column - 执行sql用于计算sum、count
* commit - 如果table为事务,该方法则会commit
* lastSql - 最近一次执行的sql语句
* lastInsertId - 最近一次insert的id
* lastError - 最近一次执行sql的error
### 条件方法
* where - where语句,<br>
例如: where('a=1 AND b=2')或者where('a="hello" OR a="world"')
* order - order by语句,<br>
例如: where('column desc')或者where(['column1 desc','column2 asc'])
* limit - limit 语句,<br>
例如: limit(0,10)
* join - join语句,<br>
例如: join('table2 AS T2')
* on - join存在时的on语句,<br>
例如: table('table1 AS T1')->join('table2 AS T2')->on('T1.id=T2.tid')->select();
* group - group by语句,<br>
例如: group('status')或者group(['status','xxx'])
### 结果方法
* count - 获得数量,<br>
例如: count()或count('id')
* sum - 获得和,<br>
例如: sum('abc')
* one - select一条,<br>
例如: $db->table('table')->where('a=1')->one()
* select - select一条(或多条),<br>
例如: $db->table('table')->where('a=1')->select();或select('column')或select(['c1','c2'])
* update - 更新<br>
例如: $db->table('table')->where('a=1')->update(['column'=>'value'])
* insert - 插入<br>
例如: $db->table('table')->insert(['column1'=>'value1','column2'=>'value2'])
* insertMulti - 插入多条<br>
例如: $db->table('table')->insertMulti(['column1','column2'],[['value11','value12'],['value21','value22']])
* delete - 删除<br>
例如: $db->table('table')->where('a=1')->delete()
### 事务操作.
```
$db = LDB::create([
    'host' => '127.0.0.1',
    'port' => 3306,
    'username' => 'root',
    'password' => '*****',
    'database' => 'task',
    'prefix' => ''
]);

$db->beginTransaction();

$result = $db->table('list')->where(['a'=>1])->update(['b'=>2]);
$result = $db->table('list')->insert(['b'=>2]);
$result = $db->table('list')->insert(['b'=>3]);

$result = $db->commit();
var_dump($result);
```
##LActiveRecord (基于LDBKernel)
提供面向对象的方式用以访问数据库中的数据。一个LActiveRecord类关联一张数据表， 每个对象对应表中的一行，对象的属性映射到数据行的对应列。
###声明一个AR类
实现tableName方法, 返回表名
```
use Base\Orm\LActiveRecord;

/**
 * Class Task
 * @property $id
 * @property $name
 * @property $ip
 * @property $port
 * @property $create_time
 */
class Task extends LActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return "p_task";
    }
}
```
###初始化对象(构造方法传入LDB对象)
```
* $model = new Task($db);
```
###插入一行数据
```
$model = new Task($db);
$model->name = 'test task';
$model->ip = "127.0.0.1";
$model->port = '22';
$model->create_time = date('Y-m-d H:i:s');
$model->save();  // 一行新数据插入task表
```
###链式查询
find方法返回一个LActiveQuery对象, 能使用所有LDBKernel的链式查询方法
```
$query = $model->find();
$query->where(['name'=>'test task'])->andWhere("ip='127.0.0.1'");
```
###与LDBKernel不同的是, select返回对象数组, one返回单个对象
```
$tasks =$query->select();
$task = $query->one();
```
###query调用asArray()方法返回数组, 与LDBKernel执行结果一样
```
$tasksArray = $query->asArray()->select();
$taskArray = $query->asArray()->one();

```
###访问LActiveRecord对象属性
* 对象形式
```
$taskObj->name
```
* 数组形式
```
$taskObj['name']
```
* foreach遍历
```
foreach ($taskObj as $key => $value) {
    echo "{$key}--{$value}";
}
```
* 获取属性数组
```
$task->toArray()
```
* 获取json
```
$task->toJson()
```
###设置属性
* 对象方式设置属性
```
$task->name = "test A";
```
* setAttribute方法设置单个属性
```
$task->setAttribute('ip','127.0.0.2');//设置单个属性
```
* setAttributes方法批量设置属性
```
$task->setAttributes(['ip' => '127.0.0.3', 'name' => 'test setAttributes']);
```
###更新到数据库
save()保存到数据库, 主键存在新增,不存在插入
```
$task->save()
```
##获取save修改之前的所有属性
```
$task->getOldAttributes()
```
##获取发生修改的属性
```
$task->getDirtyAttributes()
```
##自定义查询语句
* 新建自定义查询对象
```
/**
 * Class TaskQuery
 */
class TaskQuery extends \Base\Orm\LActiveQuery
{
    /**
     * 命名范围(自定义查询条件)
     *
     * @param $value
     * @return $this
     */
    public function name($value)
    {
        return $this->where("name='{$value}'");
    }

}
```
* 重写模型类的find()方法, 利用刚刚新建对Query对象, 如下
```
class Task extends LActiveRecord
{
    /**
     * @return string
     */
    public function tableName()
    {
        return "p_task";
    }

    /**
     * @return TaskQuery
     */
    public function find()
    {
        $activeQuery = new TaskQuery($this->_db, $this->trueTableName());
        $activeQuery->setModelClass(get_called_class());
        return $activeQuery;
    }
}
```
* 调用自定义查询方法
```
$task = $taskModel->find()->name("测试机(123.57.157.78)")->one();
```

##钩子方法
* beforeSave (save前调用,返回false将阻止save调用)
* afterSave (save后调用)