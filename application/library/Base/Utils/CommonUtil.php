<?php

namespace Base\Utils;

class CommonUtil
{
    /**
     * 获取$_GET数据
     *
     * @param string $key
     * @param string $type 数据类型 (int,float,string)
     * @param string $defaultValue
     * @return mixed
     */
    public static function get($key = null, $type = 'string', $defaultValue = '')
    {
        if (!$key) {
            return $_GET;
        }
        return self::typeConvert(isset($_GET[$key]) ? $_GET[$key] : $defaultValue, $type);
    }

    /**
     * 获取$_POST数据
     *
     * @param string $key
     * @param string $type 数据类型 (int,float,string)
     * @param string $defaultValue
     * @return mixed
     */
    public static function post($key = null, $type = 'string', $defaultValue = '')
    {
        if (!$key) {
            return $_POST;
        }
        return self::typeConvert(isset($_POST[$key]) ? $_POST[$key] : $defaultValue, $type);
    }

    /**
     * 获取$_REQUEST数据
     *
     * @param string $key
     * @param string $type 数据类型 (int,float,string)
     * @param string $defaultValue
     * @return mixed
     */
    public static function request($key = null, $type = 'string', $defaultValue = '')
    {
        if (!$key) {
            return array_merge($_GET, $_POST);
        }
        return self::typeConvert(isset($_REQUEST[$key]) ? $_REQUEST[$key] : $defaultValue, $type);
    }

    /**
     * 获取HTTP头部中埋点信息
     *
     * @param string $key 头部字段中包含的字段名
     * @return array|string
     */
    public static function header($key = '')
    {
        $keyArray = [
            'ISLOCATE',             //是否开启定位
            'MODEL',                //手机品牌型号
            'NETWORKTYPE',          //网络情况
            'OSVERSION',            //手机系统版本号
            'PHONENUMBER',          //手机号
            'PACKAGE',              //应用程序包名
            'SERIALNUMBER',         //手机序列号
            'TYPE',                 //手机系统  1：安卓，2：IOS
            'VERSION',              //福佑接口版本号
            'CLIENTOS',                //司机模块 - 手机系统  2-android， 3-ios
        ];
        $params = [];
        foreach ($_SERVER as $k => $v) {
            $arr = explode('_', $k);
            if ($arr[0] == 'HTTP' && in_array($arr[1], $keyArray)) {
                $params[$arr[1]] = rawurldecode($v);
            }
        }

        if ($key) {
            return isset($params[$key]) ? $params[$key] : '';
        } else {
            return $params;
        }
    }

    /**
     * 数据类型转换
     *
     * @param mixed $v
     * @param string $toType
     * @return mixed
     */
    public static function typeConvert($v, $toType)
    {
        switch ($toType) {
            case 'int':
                $v = (int)$v;
                break;
            case 'float':
                $v = (float)$v;
                break;
            case 'string':
                $v = (string)trim($v);
                break;
        }
        return $v;
    }

    /**
     * 获取ip地址
     *
     * @return string
     */
    public static function getIp()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                /* 取X-Forwarded-For中第x个非unknown的有效IP字符? */
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if ($ip != 'unknown') {
                        $realIp = $ip;
                        break;
                    }
                }
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realIp = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    $realIp = $_SERVER['REMOTE_ADDR'];
                } else {
                    $realIp = '0.0.0.0';
                }
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realIp = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $realIp = getenv('HTTP_CLIENT_IP');
            } else {
                $realIp = getenv('REMOTE_ADDR');
            }
        }

        if (isset($realIp)) {
            if (preg_match('/[\d\.]{7,15}/', $realIp, $onlineIp)) {
                $realIp = $onlineIp[0];
            }
        }

        return isset($realIp) ? $realIp : '0.0.0.0';
    }

    /**
     * 检测是否是合法的大陆手机号
     *
     * @param string $v
     * @return bool|int
     */
    public static function isMobile($v)
    {
        return !empty($v) ? preg_match('/^(\+?86-?|0)?1[0-9]{10}$/', $v) : false;
    }

    /**
     * 检测是否是合法的URL地址
     *
     * @param $v string
     * @return bool|int
     */
    public static function isURL($v)
    {
        return $v ? preg_match('/^(http|https)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?$/', $v) : false;
    }

    /**
     * 检测是否是合法的大陆电话号码
     *
     * @param string $v
     * @return bool|int
     */
    public static function isTel($v)
    {
        return !empty($v) ? preg_match('/(\d{4}-|\d{3}-)?(\d{8}|\d{7})/', $v) : false;
    }

    /**
     * 1. 如果距当前时间60s内,则显示x秒内
     * 2. 如果距当前时间60m内,则显示x分钟内
     * 3. 如果距当前时间24h内,则显示x小时内
     * 4. 如果超过24小时,则显示x天前
     * @param $time
     * @return string
     */
    public static function unixtimeFormat($time)
    {
        $v = time() - $time;
        $v = $v > 0 ? $v : 0;
        if ($v < 60) {
            $format = '刚刚';
        } else if ($v < 60 * 60) {
            $format = strval(floor($v / 60)) . '分钟前';
        } else if ($v < 60 * 60 * 24) {
            $format = strval(floor($v / 60 / 60)) . '小时前';
        } else {
            $format = strval(floor($v / 60 / 60 / 24)) . '天前';
        }
        return $format;
    }

    /**
     * 1. 时间戳转为 xx天xx时xx分xx秒
     * 2. 如果距当前时间60m内,则显示x分钟内
     * 3. 如果距当前时间24h内,则显示x小时内
     * 4. 如果超过24小时,则显示x天前
     * @param $seconds
     * @return string
     */
    public static function unixtimeFormatDay($seconds)
    {
        $time = '';
        if ($seconds >= 86400) {
            $time .= intval($seconds / 86400) . '天';
        }
        if ($seconds % 86400 >= 3600) {
            $time .= intval(($seconds % 86400) / 3600) . '小时';
        }
        if ($seconds % 3600 >= 60) {
            $time .= intval(($seconds % 3600) / 60) . '分钟';
        }
        return $time;
    }

    /**
     * 根据base64图片格式, 获取图片扩展名
     *
     * @param $str
     * @return string
     */
    public static function getImageExtByBase64($str)
    {
        $strInfo = @unpack('c2chars', $str);
        if ($strInfo['chars1'] == '-1' && $strInfo['chars2'] == '-40') {
            $ext = 'jpg';
        } else if ($strInfo['chars1'] == '-119' && $strInfo['chars2'] == '80') {
            $ext = 'png';
        } else {
            $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
            switch ($typeCode) {
                case 7790 :
                    $ext = 'exe';
                    break;
                case 7784 :
                    $ext = 'midi';
                    break;
                case 8297 :
                    $ext = 'rar';
                    break;
                case 255216 :
                    $ext = 'jpg';
                    break;
                case 7173 :
                    $ext = 'gif';
                    break;
                case 6677 :
                    $ext = 'bmp';
                    break;
                case 13780:
                    $ext = 'png';
                    break;
                default   :
                    $ext = 'unknown' . $typeCode;
            }
        }
        return $ext;
    }

    /**
     *  检测是否是合法的身份证号码
     *
     * copy from http://www.phpernote.com/php-function/548.html
     * @param $idcard
     * @return bool
     */
    public static function isIdnumber($idcard)
    {
        if (strlen($idcard) == 18) {
            return self::idcardChecksum18($idcard);
        } else if (strlen($idcard) == 15) {
            return self::idcardChecksum18(self::idcard15To18($idcard));
        } else {
            return false;
        }
    }

    /**
     * 将15位身份证升级到18位
     *
     * @param $idcard
     * @return bool|string
     */
    public static function idcard15To18($idcard)
    {
        if (strlen($idcard) != 15) {
            return false;
        }
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false) {
            $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
        } else {
            $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
        }
        return $idcard . self::idcardVerifyNumber($idcard);
    }

    // 18位身份证校验码有效性检查
    public static function idcardChecksum18($idcard)
    {
        if (strlen($idcard) != 18) {
            return false;
        }
        if (!preg_match('/^[0-9][0-9]{16}[0-9xX]$/', $idcard)) return false;
        return self::idcardVerifyNumber(substr($idcard, 0, 17)) == strtoupper(substr($idcard, 17, 1));
    }

    // 计算身份证校验码，根据国家标准GB 11643-1999
    public static function idcardVerifyNumber($idcardBase)
    {
        if (strlen($idcardBase) != 17) {
            return false;
        }

        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verifyNumberList = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idcardBase); $i++) {
            $checksum += intval(substr($idcardBase, $i, 1)) * $factor[$i];
        }
        $mod = $checksum % 11;
        return $verifyNumberList[$mod];
    }

    /**
     * 检测是否是合法银行卡号
     * @param string $v
     * @return bool|int
     */
    public static function isBankCard($v)
    {
        if (strlen($v) > 19 || strlen($v) < 12) return false;
        return !empty($v) ? preg_match('/^[0-9][0-9]{11}[0-9]*$/', $v) : false;
    }

    /**
     * 是否为图片
     * @param $file
     * @return bool
     */
    public static function isImage($file)
    {
        if (!$v = getimagesize($file)) {
            return false;
        }
        return in_array($v[2], [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP]);
    }


    /**
     * 判断字符串是否全是英文字母.
     * @param $value
     * @return int
     */
    public static function isAllLetters($value)
    {
        if (!$value) return false;
        return preg_match('/^[A-Za-z]+$/', $value);
    }

    /*
     * @use 组装put的 json
     *
     * */
    public static function getPutJson($type, $posts)
    {
        $json['type'] = $type;
        foreach ($posts as $item) {
            $json['data'][] = $item;
        }
        return json_encode($json);
    }

    /*
     * @use 检测字符串是否中文,且限制长度
     * @param $string 字符串
     * @param $min 最短长度
     * @param $max 最长长度
     * @return bool
     * */
    public static function checkChinese($string, $min = 0, $max = 0)
    {
        $result = false;
        if (is_string($string)) {
            if ($min > 0 && $max > $min) {//限制最小长度和最大长度
                $result = preg_match("/^[\x7f-\xff]{" . $min . "," . $max . "}$/", $string);
            } else if ($min > 0 && $max < $min) {//限制最小长度
                $result = preg_match("/^[\x7f-\xff]{" . $min . ",}$/", $string);
            } else if ($min == 0 && $max == 0) {//不限制长度
                $result = preg_match("/^[\x7f-\xff]+$/", $string);
            } else if ($max && $min == 0) {//限制最大长度
                $result = preg_match("/^[\x7f-\xff]{1," . $max . "}$/", $string);
            }
        }
    }

    /*
     * @use 隐藏字符串中间位数
     * @param $string 更换字符串
     * @param $start 开始
     * @param $end 结束
     * @param $replace  可以替换的字符串
     *
     * */
    public static function hideString($string, $start, $end, $replace = "*")
    {
        $length = strlen($string);
        $result = '';

        if ($start >= $length) {
            return $result;
        }
        if ($end <= $start) {
            return $result;
        }

        for ($i = 0; $i < $length; $i++) {
            $result .= $i >= $start && $i <= $end ? $replace : $string[$i];
        }
        return $result;
    }


    /**
     * @use  use array_map() recursively
     * @param $filter 调用方法
     * @param $data   需要处理数组
     * @return array
     */
    public static function arrayMapRecursive($filter, $data)
    {
        $result = [];
        foreach ($data as $k => $v) {
            $result[$k] = is_array($v) ? self::arrayMapRecursive($filter, $v) : call_user_func($filter, $v);
        }
        return $result;
    }

    /**
     * @use 生成指定位数的随机小写字母字符串
     * @param $len int
     * return string
     */
    public static function createRandomString($len)
    {
        $str = null;
        $strPol = "abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $len; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    /**
     * 隐藏手机号，4位数字
     *
     * @param string $mobile
     * @param int $start 隐藏开始位置，默认3
     * @return mixed|string
     */
    public static function hideMobile($mobile, $start = 3)
    {
        return $mobile ? substr_replace($mobile, '****', 3, 4) : '';
    }

    /**
     * 隐藏身份证号，11位数字
     *
     * @param string $IDCard
     * @param int $start 隐藏开始位置，默认3
     * @return mixed|string
     */
    public static function hideIDCard($IDCard, $start = 3)
    {
        return $IDCard ? str_replace(substr($IDCard, $start, 11), '***********', $IDCard) : '';
    }

    /**
     * 隐藏银行卡号，6位数字
     *
     * @param string $bankCard
     * @param int $start 隐藏开始位置，默认3
     * @return mixed|string
     */
    public static function hideBankCard($bankCard, $start = 6)
    {
        return $bankCard ? str_replace(substr($bankCard, $start, 6), '******', $bankCard) : '';
    }

    /**
     * 格式化银行卡号
     *
     * @param $str
     * @return string
     */
    public static function formatBankCard($str)
    {
        $str = (string)$str;
        preg_match('/([\d]{4})([\d]{4})([\d]{4})([\d]{4})([\d]{0,})?/', $str, $match);
        unset($match[0]);
        return implode(' ', $match);
    }
}