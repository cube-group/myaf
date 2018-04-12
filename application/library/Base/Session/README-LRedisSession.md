## LRedisSession通过redis存储用户session信息
```
<?php
use \Base\Service\LSConfig;
use \Base\Session\LRedisSession;

/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/3/13
 * Time: 上午11:06
 */
class TestLRedisSession
{
    private $session;

    public function __construct()
    {
        $this->session = new LRedisSession([
            'host'=>'127.0.0.1',
            'port'=>5379,
            'password'=>'',
            'database'=>0,
            'timeout'=>2
        ]);
    }

    public function set()
    {
        dump($this->session->set('hello', time()));
    }

    public function get()
    {
        dump($this->session->get('hello'));
        dump($this->session->getTTL());
    }
}

$t = new TestLRedisSession();
$t->set();
$t->get();
```