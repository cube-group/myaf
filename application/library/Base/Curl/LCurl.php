<?php

namespace Base\Curl;

use Exception;
use Base\Auth\LBasicAuth;
use Base\File\LFile;
use Base\Log\LLog;
use Base\Utils\URLUtil;

/**
 * http请求处理
 */
class LCurl
{
    /**
     * 已尝试的次数.
     * @var int
     */
    public $try = 0;
    /**
     * 最多尝试次数.
     * @var int
     */
    public $tryMax = 1;
    /**
     * 耗时(单位:毫秒)
     * @var int
     */
    public $useTime = 0;
    /**
     * 超时时长(单位:秒)
     */
    private $timeout = 10;
    /**
     * 当前的user-agent字符串.
     * @param $postTypeList string
     */
    private $userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:14.0) Gecko/20100101 Firefox/14.0.1";
    /**
     * 本地cookie文件
     * @param $cookieFile string
     */
    private $cookieFile = '';
    /**
     * 当前模式
     */
    private $postType = '';
    /**
     * 最近一次访问结果
     * @var string
     */
    private $result = '';
    /**
     * 最近一次访问的url地址.
     * @var string
     */
    private $finalUrl = '';


    /**
     * 是否将curl的结果进行json解析.
     * @var bool
     */
    private $jsonResultFlag = false;
    /**
     * 是否将curl的访问地址进行基础验证处理.
     * @var bool
     */
    private $basicAuthFlag = false;
    /**
     * 是否将curl的访问地址进行全局日志id处理.
     * @var bool
     */
    private $globalLogId = true;

    /**
     * @var mixed
     */
    public $curlInfo;
    /**
     * http状态码
     * @var int
     */
    public $httpCode;
    /**
     * 是否请求成功
     * @var bool
     */
    public $isSuccess = false;
    /**
     * 错误信息
     * @var bool
     */
    public $error = null;
    /**
     * 错误码
     * @var int
     */
    public $errno = 0;

    /** @var bool 是否记录原始请求信息和响应信息(带header头) */
    private $httpFormat = false;
    /** @var string 请求头信息 */
    private $requestHeader;
    /** @var string 响应头信息 */
    private $responseHeader;
    /** @var string 响应原始信息(带header头) */
    private $responseOriginalResult;
    /** @var string 请求原始信息(带header头) */
    private $requestOriginalResult;

    /**
     * post模式为multipart/form-data
     * (curl的模式发送方式)
     */
    const POST_FORM_DATA = 0;
    /**
     * post模式为x-www-form-urlencoded(key=value)
     */
    const POST_FORM_URLENCODED = 1;
    /**
     * post模式为application/json
     */
    const POST_JSON = 2;
    /**
     * post模式为raw
     */
    const POST_RAW = 3;
    /**
     * post模式为xml
     */
    const POST_XML = 4;
    /**
     * post模式为ajax
     */
    const POST_AJAX = 5;


    /**
     * LCurl constructor.
     * @param int $postType
     * @param int $timeout
     * @throws Exception
     */
    public function __construct($postType = self::POST_FORM_DATA, $timeout = 10)
    {
        if (!function_exists("curl_init")) {
            throw new Exception('undefined function curl_init');
        }
        $this->timeout = $timeout;
        $this->postType = $postType;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->result = null;
        $this->finalUrl = '';
    }

    /**
     * 设置是否将结果进行json转换.
     * @param $flag
     * @return $this
     */
    public function setJsonResult($flag)
    {
        $this->jsonResultFlag = $flag;
        return $this;
    }

    /**
     * 设置是否兼容基础验证(时间&秘钥验证).
     * @param $flag
     * @return $this
     */
    public function setBasicAuth($flag)
    {
        $this->basicAuthFlag = $flag;
        return $this;
    }

    /**
     * 设置是否兼容全局log id传递
     * @param $flag
     * @return $this
     */
    public function setGlobalLogId($flag)
    {
        $this->globalLogId = $flag;
        return $this;
    }

    /**
     * 设置post的类型.
     * 默认为POST_FORM_DATA方式
     * @param $type int
     * @return $this
     */
    public function setPostType($type)
    {
        if (!in_array($type, [0, 1, 2, 3, 4, 5])) {
            $this->postType = self::POST_FORM_DATA;
        } else {
            $this->postType = $type;
        }
        return $this;
    }


    /**
     * 设置访问超时时间时长
     * (单位:秒)
     * @param $value int
     * @return $this
     */
    public function setTimeout($value)
    {
        if (!is_numeric($value) || $value <= 0) {
            $value = 15;
        }
        $this->timeout = $value;
        return $this;
    }

    /**
     * 更改默认的ua信息
     *
     * 本方法常用于模拟各种浏览器
     *
     * @param string $userAgent UA字符串
     *
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * 设置本地cookie文件
     *
     * 在用curl来模拟时常需要设置此项
     *
     * @param string $cookieFile 文件路径
     *
     * @return $this
     */
    public function setCookieFile($cookieFile)
    {
        $this->cookieFile = $cookieFile;

        return $this;
    }


    /**
     * 返回原始数据
     * @return string
     */
    public function getOriginalResult()
    {
        return $this->result;
    }

    /**
     * 返回最近一次的完成url地址.
     * @return string
     */
    public function getFinalUrl()
    {
        return $this->finalUrl;
    }

    /**
     * 发送Get请求
     *
     * @param $url string
     * @param null|array $data
     * @param array $header
     * @return bool|mixed
     */
    public function get($url, $data = null, $header = [])
    {
        $url = URLUtil::addParameter($url, $data);
        return $this->exec($url, $data, 'get', $header);
    }

    /**
     * 发送Post请求
     *
     * @param $url string
     * @param $data mixed
     * @param array $header
     * @return bool|mixed
     */
    public function post($url, $data, $header = [])
    {
        return $this->exec($url, $data, 'post', $header);
    }

    /**
     * 发送Post文件流请求
     *
     * @param $url string
     * @param $data mixed
     * @param array $files
     * @param array $header
     * @return bool|mixed
     */
    public function postStream($url, $data, $files = [], $header = [])
    {
        if (!is_array($files)) {
            $file = $files;
            $files = ['file' => $file];
        }
        foreach ($files as $key => $item) {
            if ($streamData = LFile::readStream($item)) {
                $data["{$key}\";filename=\"{$key}"] = $streamData;
            }
        }
        return $this->exec($url, $data, 'post', $header);
    }

    /**
     * 发送Put请求
     * @param $url string
     * @param $data mixed
     * @param array $header
     * @return bool|mixed
     */
    public function put($url, $data, $header = [])
    {
        return $this->exec($url, $data, 'put', $header);
    }


    /**
     * 发送Delete请求
     * @param $url string
     * @param $data mixed
     * @param array $header
     * @return bool|mixed
     */
    public function delete($url, $data, $header = [])
    {
        return $this->exec($url, $data, 'delete', $header);
    }


    /**
     * 发送Head请求.
     * @param $url string
     * @param $data mixed
     * @param array $header
     * @return bool|mixed
     */
    public function head($url, $data = null, $header = [])
    {
        return $this->exec($url, false, 'head', $header);
    }


    /**
     * 综合请求
     * @param $url
     * @param string $data
     * @param string $method
     * @param array $header
     * @return bool|mixed
     */
    private function exec($url, $data = '', $method = 'post', $header = [])
    {
        $res = false;
        $this->try = 0;
        $this->result = '';
        $this->action('start');

        while ($this->try < $this->tryMax) {
            $this->try++;
            $res = $this->realExec($url, $data, $method, $header);
            if ($res) {
                break;
            }
        }

        $this->action('stop');
        return $res;
    }


    /**
     * 真正访问
     * @param $url
     * @param $data
     * @param $type
     * @param array $header
     * @return bool|mixed
     */
    private function realExec($url, $data, $type, $header = [])
    {
        if (!$url || !$type) {
            return false;
        }
        if (!$data) {
            $data = [];
        }
        $header = $this->getHeader($header);
        $type = strtolower($type);

        //提供全局日志id支持.
        if ($this->globalLogId) {
            $url = URLUtil::addParameter($url, [LLog::LOG_HUNTER => LLog::getGlobalRequestId()]);
        }
        //提供基础验证签名支持
        if ($this->basicAuthFlag) {
            if ($type == 'get') {
                $url = LBasicAuth::get($url);
            } else {
                $url = URLUtil::addParameter($url, ['sign' => LBasicAuth::get($data)]);
            }
        }
        $this->finalUrl = $url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($type == 'get') {

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        } else if ($type == 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            switch ($this->postType) {
                case self::POST_FORM_DATA:
                    if (!$data || !is_array($data)) {
                        $data = [];
                    }
                    break;
                case self::POST_FORM_URLENCODED:
                    if (!$data || !is_array($data)) {
                        $data = [];
                    }
                    $data = http_build_query($data);
                    $header[] = 'Content-Type: application/x-www-form-urlencoded';
                    break;
                case self::POST_JSON:
                    if (!$data) {
                        $data = '';
                    } else if (is_array($data)) {
                        $data = json_encode($data);
                    }
                    $header[] = 'Content-Type: application/json';
                    break;
                case self::POST_XML:
                    $header[] = 'Content-Type: text/xml;charset=utf-8';
                    break;
                case self::POST_RAW:
                    if (is_array($data)) $data = json_encode($data);
                    break;
                case self::POST_AJAX:
                    if (is_array($data)) $data = json_encode($data);
                    $header[] = 'X-Requested-With: XMLHttpRequest';
                    $header[] = 'Accept:application/json, text/javascript';
                    $header[] = 'Accept-Language:zh-CN,zh;q=0.8,en;q=0.6';
                    $data = urlencode($data);
                    break;
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else if ($type == 'put') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else if ($type == 'delete') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else if ($type == 'head') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
        } else {
            return false;
        }

        if ($this->userAgent) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        }
        if ($this->cookieFile) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        }
        if ($header) {
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);


        if ($this->httpFormat) {
            //CURLINFO_HEADER_OUT选项可以拿到请求头信息
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            //设置返回输出响应头
            curl_setopt($ch, CURLOPT_HEADER, true);
            $this->responseOriginalResult = curl_exec($ch);
            //获取请求头
            $this->requestHeader = curl_getinfo($ch, CURLINFO_HEADER_OUT);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            //获取响应头(根据头大小去获取响应头信息内容)
            $this->responseHeader = substr($this->responseOriginalResult, 0, $headerSize);
            if ($type == 'get') {
                $this->requestOriginalResult = $this->requestHeader;
            } else {
                $this->requestOriginalResult = $this->requestHeader . http_build_query($data);
            }
            //设置响应原始内容
            $this->result = substr($this->responseOriginalResult, $headerSize);
        } else {
            $this->result = curl_exec($ch);
        }

        $this->curlInfo = curl_getinfo($ch);
        $this->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->errno = curl_errno($ch);
        $this->error = curl_error($ch);
        $this->isSuccess = $this->httpCode == 200;

        return $this->jsonResultFlag ? json_decode($this->result, true) : $this->result;
    }

    /**
     * 处理header.
     * (支持两种header格式)
     *
     * @param $value
     * @return array
     */
    private function getHeader($value)
    {
        if (!$value) {
            return [];
        }
        $newHeader = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $newHeader[] = $key . ': ' . $item;
            } else {
                $newHeader[] = $item;
            }
        }
        return $newHeader;
    }

    /**
     * 记录动作测算耗时.
     * @param string $value
     */
    private function action($value = 'start')
    {
        if ($value == 'start') {
            $this->useTime = microtime(true);
        } else {
            $this->useTime = (int)((microtime(true) - $this->useTime) * 1000);
        }
    }


    /**
     * 获取响应原始信息(带header头)
     *
     * @return string
     */
    public function getResponseOriginalResult()
    {
        return $this->responseOriginalResult;
    }


    /**
     * 获取请求原始信息(带header头)
     *
     * @return string
     */
    public function getRequestOriginalResult()
    {
        return $this->requestOriginalResult;
    }


    /**
     * 获取请求头
     *
     * @return string
     */
    public function getRequestHeader()
    {
        return $this->requestHeader;
    }

    /**
     * 获取响应头
     *
     * @return string
     */
    public function getResponseHeader()
    {
        return $this->responseHeader;
    }

    /**
     * 是否记录原始请求信息和响应信息(带header头)
     * @param bool $flag
     */
    public function setHttpFormat($flag)
    {
        $this->httpFormat = $flag;
    }
}
