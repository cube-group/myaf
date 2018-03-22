## yaf-advance开发环境
### php扩展-yaf
Mac OS X,推荐homebrew进行安装,举例如下:
```
brew install php56-yaf
```
CentOS 6或7,推荐yum进行安装,举例如下:
```
yum -y install php-yaf
```
Linux下编译安装,以安装rabbitMQ扩展为例如下:
```
wget http://pecl.php.net/get/yaf-3.0.4.tgz
tar zxvf yaf-3.0.4.tgz
cd amqp-1.6.1
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
* tmp: 缓存目录
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

class ControllerBase extends \Core\Control {
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

### yaf中全局配置(变量)的set和get
* 但我们使用G进行了封装
* 相关操作如下:
```
//获取全局业务配置
G::get('key');
```
```
//设置全局配置
G::set('key','xxx');
```
### cli模式运行yaf
由于当版yaf代码已经被稍微处理过,运行方式如下:
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
### 错误捕捉
* Dispatcher::getInstance()->catchException(true);用于开启错误捕捉
* 会被application/controllers/Error.php的errorAction自动捕捉到
### yaf框架流程
* 请求进入nginx被转到fast_cgi的fpm接收
* 进入到public/index.php
* 进入到application/Bootstrap.php
* Bootstrap中按照从上往下执行_init开头的函数
* 执行各种_initPlugins中的插件(hook)
* Router解析出module、controller、action之后命中相应的controller文件
* 如果没有命中则会命中相应的module中的ErrorController::errorAction
* 如果相应的module中没有ErrorController则命中application/controllers/Error.php中的errorAction