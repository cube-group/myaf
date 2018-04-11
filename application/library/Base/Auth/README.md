## LAuth标准接口统一验证(基于时间戳和秘钥)
### get-生成校验过的url或queryString
```
$url = 'http://www.xxx.com/a/b/c?a=1';
$url = LAuth::get($url);
```
$url被处理为http://www.xxx.com/a/b/c?a=1&sysKey=2&reqTimeStamp=1484813846&authString=7752da15a92e6ca6a277ea305f090ecd
```
$url = 'http://www.xxx.com/a/b/c?a=1';
$query = LAuth::get();
$url .= '&'.$query;
```
$query为sysKey=2&reqTimeStamp=1484813846&authString=7752da15a92e6ca6a277ea305f090ecd

### check-校验传入的$_GET/$_POST/url地址是否通过校验
```
$bool = LAuth::check($_GET);
```
```
$bool = LAuth::check($_POST);
```
```
$bool = LAuth::check('http://www.xxx.com/a/b/c?a=1&sysKey=2&reqTimeStamp=1484813846&authString=7752da15a92e6ca6a277ea305f090ecd');
```

## LBasicAuth标准接口统一验证(仅基于秘钥)
### 生成sign签名
```
$arr = ['p1'=>'a','p2'=>2];
$sign = LBasicAuth::get($arr);
```
### check进行参数和签名校验
```
$post = $_POST;
$sign = $_GET['sign'];
$bool = LBasicAuth::check($sign,$post);
```