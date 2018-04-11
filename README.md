## yaf基础封装快速开发环境
### php扩展-yaf
Mac OS X,推荐homebrew进行安装,举例如下:
```
brew install php70-yaf
```
CentOS 6或7,推荐yum进行安装,举例如下:
```
yum -y install php-yaf
```
Linux下编译安装扩展如下:
```
wget http://pecl.php.net/get/yaf-3.0.6.tgz
tar zxvf yaf-3.0.6.tgz
cd yaf-3.0.6
/usr/local/php/bin/phpize
./configure --with-php-config=/usr/local/php/bin/php-config
make
make install
```
### yaf所需的php.ini配置
```
[yaf]
;;yaf使用命名空间
yaf.use_namespace=1
;;自动载入类找不到之后是否启动php默认类导入机制
yaf.use_spl_autoload=1
;;是否缓存配置文件(只针对INI配置文件生效), 打开此选项可在复杂配置的情况下提高性能
yaf.cache_config=1
;;yaf扩展
extension="/usr/local/opt/php56-yaf/yaf.so"
```
### yaf目录说明
* application: 业务核心代码
* bin: cli模式下的框架入口,如:php bin/cli xx/xx
* conf: 配置文件目录
* public: web静态文件目录和index.php入口
### application目录说明
* controllers: 路由目录
* library: 本地类库代码目录
* models: 模型目录
* modules: 多模块目录(必须在conf/application.ini中开启application.modules='xx,xx'才会开启)
* plugins: 插件目录
* views: 模板目录
### yaf类自动引用机制
yaf追求类导入简单化,在application目录下的controllers、library、models、plugins
里的标准命名类都无需require导入直接use即可自动获取。
* library命名机制:
```
namespace Core;

abstract class Control extends Controller_Abstract{
}
```
* controllers命名机制:Index.php
```
use \Core\Control;

class IndexController extends Control{
    public function indexAction(){}
}
```
* models命名机制:User.php
```
class UserModel{
    public function getUserInfo(){}
}
```
* plugins命名机制:Auth.php
```
class AuthPlugin extends \Yaf\Plugin_Abstract{
}
```

### application/Bootstrap.php是什么?
它是框架程序的一个入口.
```
define('APP_PATH',__DIR__.'/..');
define('APP_CONFIG',APP_PATH.'/conf/application.ini');

$app = new \Yaf\Application(APP_CONFIG);
$app->bootstrap()->run();
```
以上代码中$app->bootstrap()将会自动执行application/Bootstrap.php
Bootstrap.php中的所有以_init开头的函数会按照顺序自上而下执行。
### 全局配置(变量)的set和get
* 但我们使用G进行了封装
* 相关操作如下:
获取配置
```
G::get('key');
```
设置配置
```
G::set('key','xxx');
```
### 获取配置文件conf/application.conf内容
方式一:
```
G::conf()->get('application.name');
```
方式二:
```
G::conf()->application->name;
```
获取array:
```
G::conf()->get('application')->toArray();
G::conf()->application->toArray();
```
### 以cli模式运行yaf
```
//单模块模式
$php bin/cli controller/action p1
//单模块多参数模式
$php bin/cli controller/action p1 p2
//多模块模式
$php bin/cli module/controller/action p1
//多模块多参数模式
$php bin/cli module/controller/action p1 p2
```
注意:
1. 单参数时,action函数接收的是值
2. 多参数时,action函数接受的是数组
### 框架错误捕捉
* common.error.report - 配置当前环境下的错误等级
* common.error.display - 配置当前环境下是否报错(该选项作用不大)
* cli模式下框架如果遇到错误会强制抛出错误
* index模块中错误会被转到application/controllers/Error.php的errorAction
* 非index模块中错误,会优先检测该模块中的controllers/Error.php,如果没有则命中application/controllers/Error.php的errorAction
### 框架流程
* 请求进入nginx被转到fast_cgi的fpm接收
* 进入到public/index.php
* 进入到application/Bootstrap.php
* Bootstrap中按照从上往下执行_init开头的函数
* 执行各种_initPlugins中的插件(hook)
* Router解析出module、controller、action之后命中相应的controller文件
* 如果没有命中则会命中相应的module中的ErrorController::errorAction
* 如果相应的module中没有ErrorController则命中application/controllers/Error.php中的errorAction
### 操作mysql
* SELECT
```
$table = Data::db('default')->table('users');
$result = $table->where(['a'=>1])->limit(0,1)->order('ASC')->group('name')->select(['id','name']);
var_dump($result);
//$result是多条
$result = $table->where(['a'=>1])->andWhere('a>1')->orWhere('b'=>[1,2,3])->one();
var_dump($result);
//$result是单条
```
* UPDATE
```
$table = Data::db('default')->table('users');
$result = $table->where(['user' => 'a'])->update(['type' => 1]);
```
* INSERT
```
$db = Data::db('default');
$result = $db->table('list2')->insert(['type' => 1, 'user' => 'linnn']);
var_dump($db->lastSql(), $db->lastInsertId(), $db->lastError());
$result = $db->table('list2')->insertMulti(['type', 'user'], [[2, time() . '-hello'], [3, time() . '-hello'], [3, time() . '-hello']]);
var_dump($db->lastSql(), $db->lastInsertId(), $db->lastError());
```
* DELETE
```
$db = Data::db('default');
$result = $db->table($name)->where($params)->delete()<br>');
var_dump($db->lastSql(), $db->lastInsertId(), $db->lastError());
```
### 操作redis
```
$result = Data::redis()->hGetAll('ssid-32f84c1912100c85eaf6c2db619d3ee6');
```
### 操作memcache
```
$result = Data::memcache()->get('xxx');
```
### 操作mongodb
```
$rt = Data::mongo()->model('collect')->insert(['a' => 1]);
var_dump($rt);
$rt = Data::mongo()->model('collect')->findOne(['a' => 1]);
var_dump($rt);
```
### 操作redis队列
coming soon
### 操作rabbitmq队列(HTTP RESTFUL API)
coming soon...
### 操作log
```
//LLog初始化,app名称为name,日志存储路径为/data/log/name,非debug
LLog::init('name', '/data/log/name', 'Asia/Shanghai', false);
//debug日志
LLog::info('功能1',__FILE__,'HelloWorld');
LLog::info('功能1',__FILE__,'Json:',['status'=>'Y']);
//错误日志
LLog::error('功能2',__FILE__,'SendData:',['a'=>'123123']);
//...logic
//日志压栈存储
LLog::flush();
```
详细查看Log包中的README
### 更多工具包
* 请到根目录执行composer install
* phpbase: https://github.com/cube-group/phpbase
* lvalidator: https://github.com/cube-group/lvalidator
* 以上轮子包基本够PHP研发使用
* 轮子工具包命名空间以\libs开头
