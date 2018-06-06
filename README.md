## Miss You Yet Another Framework
so fast and fast ...
### php版本要求
```
>= 5.6
```
### 1. 安装扩展yaf
Mac OS X,推荐homebrew进行安装,举例如下:
```shell
brew install php70-yaf
```
CentOS 6或7,推荐yum进行安装,举例如下:
```shell
yum -y install php-yaf
```
Linux下编译安装扩展如下:
```shell
wget http://pecl.php.net/get/yaf-3.0.6.tgz
tar zxvf yaf-3.0.6.tgz
cd yaf-3.0.6
/usr/local/php/bin/phpize
./configure --with-php-config=/usr/local/php/bin/php-config
make
make install
```
### 2. yaf所需php.ini配置
```ini
[yaf]
;;yaf使用命名空间
yaf.use_namespace=1
;;自动载入类找不到之后是否启动php默认类导入机制
yaf.use_spl_autoload=1
;;是否缓存配置文件(只针对INI配置文件生效), 打开此选项可在复杂配置的情况下提高性能
yaf.cache_config=1
;;yaf扩展
extension="yaf.so"
```
### 3. yaf目录和常量说明
主目录:<br>
* application: 业务核心代码
* bin: cli模式下的框架入口,如:php bin/cli xx/xx
* conf: 配置文件目录
* public: web静态文件目录和index.php入口

application目录:<br>
* controllers: 路由目录
* library: 本地类库代码目录(必須在conf/application.ini中開啟application.libraryNamespace="xx,xx"才會啟用)
* models: 模型目录
* modules: 多模块目录(必须在conf/application.ini中开启application.modules='xx,xx'才会开启)
* plugins: 插件目录
* views: 模板目录

常量Constants:<br>
* APP_MODE 环境标识,如:develop、production
* APP_PATH 项目根目录application
* APP_CONFIG 当前环境下业务配置文件地址
* FRAMEWORK_ERR_NOTFOUND_MODULE 错误码515
* FRAMEWORK_ERR_NOTFOUND_CONTROLLER 错误码516
* FRAMEWORK_ERR_NOTFOUND_ACTION 错误码517
* FRAMEWORK_ERR_NOTFOUND_VIEW 错误码518

### 4. yaf请求流入顺序
1. 请求进入nginx被转发到fastcgi的fpm的子进程接收
2. 初始化各种php配置、载入扩展、函数指针等
3. 进入public/index.php并new \Yaf\Application实例
4. 读入conf/application.ini配置文件
5. 进入application/Bootstrap.php文件
6. Bootstrap中按照从上往下执行以_init开头的函数
7. 执行各种_initPlugins中的插件(<a href='http://www.laruence.com/manual/yaf.plugin.times.html'>yaf支持的hook</a>)可以配合框架中的Router.php和Static.php两个插件文件进行学习
8. Router路由功能解析出module、controller、action并命中相应的controller文件
9. 若未命中则会命中相应module中的ErrorController::errorAction
10. 若相应的module中没有ErrorController则命中application/controllers/Error.php中的ErrorController::errorAction

### 5. application/Bootstrap.php是什么?
它是框架程序正式逻辑的入口,可以放入各种初始化配置(不过在myaf中你没必要进行关注)
```php
define('APP_PATH',__DIR__.'/..');
define('APP_CONFIG',APP_PATH.'/conf/application.ini');
$app = new \Yaf\Application(APP_CONFIG);
$app->bootstrap()->run();
```
以上代码中$app->bootstrap()将会自动执行application/Bootstrap.php
Bootstrap.php中的所有以_init开头的函数会按照顺序自上而下执行。
### 6. yaf类名严格约束
yaf追求类导入简单化和命名严格化,在application目录下的controllers、library、models、plugins
里的标准命名类都无需require导入直接use即可自动获取。
* controllers命名机制:文件名称必须为如Index.php(不带Controller)

```php
use Myaf\Core\WebController;
class IndexController extends WebController{
    public function indexAction(){}
}
```
* models命名机制:文件名称必须为如User.php(不带Model)

```php
class UserModel{
    public function getUserInfo(){}
}
```
* plugins命名机制:文件名称必须为如Auth.php(不带Plugin)

```php
use \Yaf\Plugin_Abstract
class AuthPlugin extends Plugin_Abstract{
}
```
### 7. myaf基类Control
* 永远别动以下几个类:)
* Myaf\Controller - 抽象Control基类
* Myaf\ConsoleController - 专门用于cli的Control类
* Myaf\RestController - 专门用于web的restful风格的Control类
* Myaf\WebController - 专门用于web的传统风格的Control类(最常用)

### 8. 你最喜欢的便捷路由r
* 我们支持最快速的url get参数"r"进行路由指定
* index.php?r=test/data/info,就会命中Module为Test,Controller为DataController,Action为infoAction的函数
* 当然此类路由方式尽量用于测试不建议用于生产环境 :)

### 9. myaf的restful接口处理
以application/modules/Test/controllers/Rest.php为例<br>
curl -X GET index.php?r=test/rest/users, 则命中GET_usersAction<br>
curl -X POST index.php?r=test/rest/users, 则命中POST_usersAction<br>
如果没有命中任何Action函数则命中_404Action<br>
```php
<?php

use Myaf\Core\RestController;

/**
 * Class RController
 * Restful Mode
 */
class RController extends RestController
{
    public function _404Action()
    {
    }

    public function GET_usersAction()
    {
    }

    public function POST_usersAction()
    {
    }
}
```
### 10. 全局临时存储小工具
* 它使用G进行了封装和承载
* 获取配置变量:

```php
\Myaf\Core\G::get('key');
```
* 设置配置变量:

```php
\Myaf\Core\G::set('key','xxx');
```
### 11. yaf简单而又强大的配置文件
* 文件位置:conf/application.ini
* 配置文件中直接支持DEFINE常量,如下配置片段中的APP_PATH就是常量

```ini
[common];公共配置
application.version = "v1.0.1"
application.name = "MyApp"
application.directory = APP_PATH"/application"
application.bootstrap = APP_PATH"/application/Bootstrap.php"
```
* 配置域[common] 代表公共配置
* 开发环境配置[develop:common]代理develop环境配置并且继承了common所有配置

```php
//index.php或者cli中
$app = new \Yaf\Application(APP_CONFIG, APP_MODE);
//其中APP_MODE如果为develop则会命中[develop:common]的所有配置
//其中APP_MODE如果为production则会命中[production:common]的所有配置
```
### 12. 获取配置文件信息
方式一: 函数式
```php
\Myaf\Core\G::conf()->get('application.name');
```
方式二: 优雅式
```php
\Myaf\Core\G::conf()->application->name;
```
以array方式获取子集配置:
```php
\Myaf\Core\G::conf()->get('application')->toArray();
\Myaf\Core\G::conf()->application->toArray();
```
### 13. 多模块支持
* conf/application.ini中进行配置

```ini
#支持的模块
application.modules = "Index,Test"
#默认module,Index模块为application根目录下的controllers
application.dispatcher.defaultModule = "Index"
```
* 子模块需要在application/modules中编写

### 14. cli模式
* 单模块单参数模式

```shell
$php bin/cli controller/action p1
```
* 单模块多参数模式

```shell
$php bin/cli controller/action p1 p2
```
* 多模块单参数模式

```shell
$php bin/cli module/controller/action p1
```
* 多模块多参数模式

```shell
$php bin/cli module/controller/action p1 p2
```
注意:
1. 单参数时,action函数接收的是值
2. 多参数时,action函数接受的是数组
3. 接受方式如下:
```php
class CmdController extends ConsoleController
{
    public function indexAction()
    {
        $this->json(['value' => G::route(), 'params' => $this->params()]);
    }
}
```

### 15. 模板渲染
* yaf中的默认模板文件扩展名是phtml(当然可以在application.ini中进行修改)

```ini
application.ext = "php"
application.view.ext = "phtml"
```
* view渲染demo

以application/controllers/Index.php为例<br>
它对application/views/index/index.phtml文件进行了渲染<br>
在index.phtml模板中可以直接使用$value变量
```php
<?php

use Core\ControlWeb;
use Core\G;

/**
 * Class IndexController.
 */
class IndexController extends WebController
{
    public function indexAction()
    {
        $this->display('index', ['value' => G::route()]);
    }
}
```
### 16. 框架错误捕捉
* common.error.report - 配置当前环境下的错误等级
* common.error.display - 配置当前环境下是否报错(该选项作用不大)
* cli模式下框架如果遇到错误会强制抛出错误
* index模块中错误会被转到application/controllers/Error.php的ErrorController::errorAction
* 其它模块错误会优先到该模块的controllers/Error.php,若未命中application/controllers/Error.php的ErrorController::errorAction

### 17. 操作mysql
Data::db($name);$name默认为default,会在application.ini进行关联<br>
主从配置时Data::db会自动根据curd方式进行选择主还是从,无需额外操作
```ini
;mysql单主配置
mysql.default.type = "mysql"
mysql.default.host = "127.0.0.1"
mysql.default.port = 3306
mysql.default.database = "test"
mysql.default.username = "linyang"
mysql.default.password = "linyang"
mysql.default.prefiex = ""
mysql.default.charset = "utf8"
;mysql主从分离式配置
;mysql default master config
mysql.demo.master.type = "mysql"
mysql.demo.master.host = "127.0.0.1"
mysql.demo.master.port = 3306
mysql.demo.master.database = "test"
mysql.demo.master.username = "linyang"
mysql.demo.master.password = "linyang"
mysql.demo.master.prefiex = ""
mysql.demo.master.charset = "utf8"
;mysql default slave config
mysql.demo.slave.type = "mysql"
mysql.demo.slave.host = "127.0.0.1"
mysql.demo.slave.port = 3306
mysql.demo.slave.database = "test"
mysql.demo.slave.username = "root"
mysql.demo.slave.password = "root"
mysql.demo.slave.prefiex = ""
mysql.demo.slave.charset = "utf8"
```
* SELECT

```php
use Myaf\Pool\Data;

$table = Data::db('default')->table('users');
$result = $table->where(['a'=>1])->limit(0,1)->order('ASC')->group('name')->select(['id','name']);
var_dump($result);
//$result是多条
$result = $table->where(['a'=>1])->andWhere('a>1')->orWhere('b'=>[1,2,3])->one();
var_dump($result);
//$result是单条
```
* UPDATE

```php
$table = Data::db('default')->table('users');
$result = $table->where(['user' => 'a'])->update(['type' => 1]);
```
* INSERT

```php
$db = Data::db('default');
$result = $db->table('list2')->insert(['type' => 1, 'user' => 'linnn']);
var_dump($db->lastSql(), $db->lastInsertId(), $db->lastError());
$result = $db->table('list2')->insertMulti(['type', 'user'], [[2, time() . '-hello'], [3, time() . '-hello'], [3, time() . '-hello']]);
var_dump($db->lastSql(), $db->lastInsertId(), $db->lastError());
```
* DELETE

```php
$db = Data::db('default');
$result = $db->table($name)->where($params)->delete()<br>');
var_dump($db->lastSql(), $db->lastInsertId(), $db->lastError());
```
* 执行复杂SQL
LDB提供了底层sql执行

```php
$db = Data::db('default');
$result = $db->query('select ?,? from table',['id','username']);
```
* 以ORM形式执行sql
详见LActiveRecord

### 17.5 操作session
我们强烈建议您放弃$_SESSION,因为基于docker多点容器分布是不定向传递的
```php
Data::session($name);//$name默认是session,$name会在application.ini进行关联<br>
```
配置文件片段:
```ini
redis.session.host = "127.0.0.1"
redis.session.port = 6379
redis.session.database = 1
redis.session.password = ""
redis.session.timeout = 2
```

### 18. 操作redis
```php
Data::redis($name);$name默认为default,$name会在application.ini进行关联<br>
```
配置文件片段:
```ini
;redis config
redis.default.host = "127.0.0.1"
redis.default.port = 6379
redis.default.database = "0"
redis.default.password = ""
redis.default.timeout = 2
```
Demo:
```php
$result = Data::redis()->hGetAll('ssid-32f84c1912100c85eaf6c2db619d3ee6');
```
### 19. 操作memcache
Data::redis($name);$name默认为default,$name会在application.ini进行关联<br>
配置文件片段:
```ini
;memcache config
memcache.default.host = "127.0.0.1"
memcache.default.port = 11211
memcache.default.timeout = 2
```
Demo:
```php
$result = Data::memcache()->get('xxx');
```
### 20. 操作mongodb
Data::mongo($name);$name默认为default,$name会在application.ini进行关联<br>
配置文件片段:
```ini
;mongo
mongo.default.url = "mongodb://127.0.0.1:27017"
mongo.default.username = "superadmin"
mongo.default.password = "123456"
mongo.default.database = "test"
mongo.default.tls = false
```
Demo:
```php
$rt = Data::mongo()->model('collect')->insert(['a' => 1]);
var_dump($rt);
$rt = Data::mongo()->model('collect')->findOne(['a' => 1]);
var_dump($rt);
```
### 21. 操作redis队列
Data::mqRedis($name);$name默认为default,$name会在application.ini进行关联<br>
配置文件片段:跟redis一致
```php
$queue = Data::mqRedis();
//生产
$bool = $queue->product('hello','channel_router_key');
var_dump($bool);
//消费
$message = $queue->consume('channel_router_key');
//处理消费数据...
//最近一条消费数据是否在业务上成功,false则不会删除这条数据,true会删除
$queue->consumeStatus(true);//or false
```
### 22. 操作rabbitmq队列(HTTP RESTFUL API)
Data::mqHttpRabbit($name);其中$name在application.ini中配置
```php
$queue = Data::mqHttpRabbit();
//生产
$bool = $queue->product('hello','channel_router_key');
var_dump($bool);
//消费5条
$message = $queue->consume('channel_router_key', 5);
//处理消费数据...
//最近一条消费数据是否在业务上成功,false则不会删除这条数据,true会删除
$queue->consumeStatus(true);//or false
```
### 23. 操作rabbitmq队列(以amqp扩展形式)
Data::mqRabbit($name);其中$name在application.ini中配置
```php
$queue = Data::mqRabbit();
//生产
$bool = $queue->product('hello','channel_router_key');
var_dump($bool);
//消费
$message = $queue->consume('channel_router_key');
//处理消费数据...
//最近一条消费数据是否在业务上成功,false则不会删除这条数据,true会删除
$queue->consumeStatus(true);//or false
```
### 24. 操作log
你可以在任意Controller、Model类中使用Log
```php
use Myaf\Log\Log;

//Log初始化,app名称为name,日志存储路径为/data/log/name,非debug
Log::init('name', '/data/log/name', 'Asia/Shanghai', false);
//但是myaf框架已經在G類中初始化過了只需要Log::info或debug或error即可
//debug日志
Log::info('功能1',__FILE__,'HelloWorld');
Log::info('功能1',__FILE__,'Json:',['status'=>'Y']);
//错误日志
Log::error('功能2',__FILE__,'SendData:',['a'=>'123123']);
//...logic
//日志压栈存储
Log::flush();
```
详细查看Log包中的README
### 25. 定时任务
* 详见https://hub.docker.com/r/lin2798003/apn/
* 项目根目录支持cron.json

### 26. 网络请求工具
\Myaf\Net/LCurl<br>
* 支持常见的get、post、put、head、delete请求
* 支持的post data格式有form-data、form-urlencoded、json、raw、xml、ajax

### 27. 分页小工具
\Myaf\Utils\PageUtil<br>
```
//如下总条数100,每页显示10条,当前页码为8,get参数集为uid=3
//getPagination会输出bootstrap ul元素
use \Myaf\Utils\PageUtil;

echo PageUtil::create(100, 10, 8, ['uid' => 3])->getPagination('/index');
```
### 28. yaf ide帮助
https://github.com/elad-yosifon/php-yaf-doc

### 29.更多工具包
* 框架核心包: https://github.com/cube-group/myaf-core
```
composer install cube-group/myaf-core
```

* 日志包: https://github.com/cube-group/myaf-log
```
composer install cube-group/myaf-log
```

* 网络处理包: https://github.com/cube-group/myaf-net
```
composer install cube-group/myaf-net
```
* 工具包: https://github.com/cube-group/myaf-utils
```
composer install cube-group/myaf-utils
```

* 验证包: https://github.com/cube-group/myaf-validator
```
composer install cube-group/myaf-validator
```

* 图形处理: https://github.com/cube-group/myaf-image
```
composer install cube-group/myaf-image
```

* 文档工具: https://github.com/cube-group/myaf-doc (coming soon...)
* pdf: https://github.com/cube-group/myaf-pdf (coming soon...)