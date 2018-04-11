<?php

namespace Base\Queue;

use Exception;
use Base\Curl\LCurl;

/**
 * Class LHttpRabbitMQ
 * RabbitMQ HTTP API
 * @package Base\Queue
 */
class LHttpRabbitMQ implements IMQ
{
    /**
     * @var string
     */
    private $vHost;
    /**
     * @var string
     */
    private $exchange;
    /**
     * @var string
     */
    private $conn;
    /**
     * @var string
     */
    private $auth;
    /**
     * @var string
     */
    private $queueName = '';

    /**
     * LRabbitMQ constructor.
     *
     * [
     * 'host' => '192.168.1.93',
     * 'port' => '5672',
     * 'login' => 'guest',
     * 'password' => 'guest',
     * 'database' => 'exchange_name',
     * 'vhost' => '/'
     * ]
     *
     * @param $options array
     */
    public function __construct($options = null)
    {
        $this->init($options);
    }

    /**
     * initialize
     * @param $options
     * @throws Exception
     */
    protected function init($options)
    {
        if (!$options) {
            return;
        }

        $this->vHost = $options['vhost'];
        $this->exchange = (isset($options['database']) && $options['database']) ? $options['database'] : 'amq.default';
        $this->conn = 'http://' . $options['host'] . ':' . $options['port'] . '/api';
        $this->auth = 'Basic ' . base64_encode($options['login'] . ':' . $options['password']);
    }

    public function product($message, $queue)
    {
        if (!$queue || !$message) {
            return false;
        }
        $result = $this->exec(
            '/exchanges/' . $this->vHost . '/' . $this->exchange . '/publish',
            'post',
            [
                'payload' => $message,
                'payload_encoding' => 'string',
                'routing_key' => $queue,
                'vhost' => $this->vHost,
                'name' => $this->exchange,
                'properties' => ['delivery_mode' => 1, 'headers' => []],
                'props' => [],
                'headers' => []
            ]
        );
        if ($result && isset($result['routed']) && $result['routed'] == true) {
            return true;
        }
        return false;
    }

    /**
     * 消费队列.
     * @param $queue string (rabbit中代表routingKey,redis中代表list的keyName)
     * @param $count int 消费条数
     * @return mixed
     */
    public function consume($queue, $count = 1)
    {
        if (!$queue) {
            return false;
        }
        $result = $this->exec(
            '/queues/' . $this->vHost . '/' . $queue . '/get',
            'post',
            [
                'requeue' => 'false',
                'vhost' => $this->vHost,
                'encoding' => 'auto',
                'count' => $count,
                'ackmode' => 'ack_requeue_false'
            ]
        );

        if ($result && is_array($result)) {
            $this->queueName = $queue;
            $messages = [];
            foreach ($result as $item) {
                $messages[] = $item['payload'];
            }
            return (!$messages || count($messages) > 1) ? $messages : $messages[0];
        }
        return false;
    }

    public function consumeStatus($flag = true)
    {
        throw new Exception('Method Not Allowed');
    }

    public function reQueue($message)
    {
        if ($this->queueName) {
            return $this->product($message, $this->queueName);
        }
        return false;
    }

    public function close()
    {
        throw new Exception('Method Not Allowed');
    }

    /**
     * 获取vhost下的所有exchange列表
     * @return bool|mixed
     */
    public function listExchanges()
    {
        return $this->exec('/exchanges/' . $this->vHost);
    }

    /**
     * 获取vhost下的所有channel列表
     * @return bool|mixed
     */
    public function listChannels()
    {
        return $this->exec('/vhosts/' . $this->vHost . '/channels');
    }

    /**
     * 获取vhost下的所有队列列表
     * @return bool|mixed
     */
    public function listQueues()
    {
        return $this->exec('/queues/' . $this->vHost);
    }

    /**
     * real request
     * @param $route
     * @param string $action
     * @param null $data
     * @return bool
     */
    private function exec($route, $action = 'get', $data = null)
    {
        $url = $this->conn . $route;
        $curl = new LCurl(LCurl::POST_JSON);
        $result = $curl->setTimeout(5)
            ->setJsonResult(true)
            ->$action($url, $data, ['Authorization' => $this->auth]);
        if ($result && !$curl->error) {
            return $result;
        }
        return false;
    }
}