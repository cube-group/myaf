# Curl工具类库
### 常用属性
* 请求尝试的次数: $curl->try
* 请求尝试的最大次数: $curl->tryMax
* 请求耗时(单位:毫秒): $curl->useTime
### 常规用法
```
use Base\Curl\LCurl;

$curl = new LCurl();
$curl->post('http://google.com',['a'=>'1'],['X-Token'=>'test'],15);
$curl->post('http://google.com','<xml><item><a>1</a></item></xml>');
$curl->get('http://google.com');
$curl->get('http://google.com',['a'=>'1']);
```
### 设定尝试次数
```
use Base\Curl\LCurl;
$curl = new LCurl(['tryMax'=>3]);
$curl->post('http://google.com',['a'=>'1'],['X-Token'=>'test'],15);
echo 'try: '.$curl->try;
```