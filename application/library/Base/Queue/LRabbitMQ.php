<?php
namespace Base\Queue;

use \AMQPConnection;
use \AMQPExchange;
use \AMQPChannel;
use \AMQPQueue;
use \AMQPQueueException;
use \AMQPConnectionException;
use \AMQPExchangeException;
use \AMQPEnvelope;
//use Exception;
//use Base\Validate\LValidator;


/**
 * Class LRabbitMQ
 * @package Base\Queue
 */
class LRabbitMQ implements IMQ
{
    /**
     * @var AMQPConnection
     */
    private $conn;
    /**
     * @var AMQPChannel
     */
    private $channel;
    /**
     * @var AMQPExchange
     */
    private $exchange;
    /**
     * @var AMQPQueue
     */
    private $queue;
    /**
     * @var string
     */
    private $exchangeName = '';
    /**
     * @var string
     */
    private $queueName = '';
    /**
     * @var AMQPEnvelope
     */
    private $message;

    /**
     * LRabbitMQ constructor.
     * @param $options array
     */
    public function __construct($options = null)
    {
        /**
         * [
         * 'host' => '192.168.1.93',
         * 'port' => '5672',
         * 'login' => 'guest',
         * 'password' => 'guest',
         * 'database' => 'exchange_name',
         * 'vhost' => '/'
         * ]
         */
        $this->init($options);
    }

    protected function init($options)
    {
        if (!$options) {
            return;
        }
//        $val = new LValidator($options);
//        $val->rules([
//            ['required', 'host'],
//            ['required', 'port'],
//            ['required', 'login'],
//            ['required', 'password'],
//            ['required', 'vhost']
//        ]);
//        if (!$val->validate()) {
//            throw new Exception('LHttpRabbitMQ options invalid');
//        }

        $this->exchangeName = (isset($options['database']) && $options['database']) ? $options['database'] : 'amq.default';
        try {
            $this->conn = new AMQPConnection($options);
            if (!$this->conn->connect()) {
                $this->conn = null;
                return;
            }

            $this->channel = new AMQPChannel($this->conn);
            if (!$this->channel->isConnected()) {
                $this->close();
                return;
            }

            $this->exchange = new AMQPExchange($this->channel);
            $this->exchange->setName($this->exchangeName);
            $this->exchange->setType(AMQP_EX_TYPE_DIRECT);
            $this->exchange->setFlags(AMQP_DURABLE);
            $this->exchange->declareExchange();
        } catch (\AMQPConnectionException $e) {
            $this->conn = null;
        } catch (\AMQPExchangeException $e) {
            $this->close();
        }
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->close();
    }

    public function product($message, $queue)
    {
        if (!$queue || !$message) {
            return false;
        }
        if (!$this->channel) {
            return false;
        }
        if ($this->queueName != $queue) {
            if ($this->queue) {
                try {
                    $this->queue->cancel();
                } catch (\AMQPQueueException $e) {
                }
            }
            $this->queueName = $queue;
            $this->queue = new AMQPQueue($this->channel);
            $this->queue->setName($queue);
            $this->queue->setFlags(AMQP_DURABLE); //持久化
            $this->queue->declareQueue();
            if (!$this->queue->bind($this->exchangeName, $queue)) {
                return false;
            }
        }

        //$channel->startTransaction(); //开始事务
        return $this->exchange->publish($message, $queue);
        //$channel->commitTransaction(); //提交事务
    }

    /**
     * 消费队列.
     * @param $queue string (rabbit中代表routerKey,redis中代表list的keyName)
     * @param $count int 消费条数
     * @return mixed
     */
    public function consume($queue, $count = 1)
    {
        if (!$queue) {
            return false;
        }
        if (!$this->channel) {
            return false;
        }
        if ($this->queue) {
            try {
                $this->queue->cancel();
            } catch (\AMQPQueueException $e) {
            }
        }
        if ($this->queueName != $queue) {
            $this->queueName = $queue;
            $this->queue = new AMQPQueue($this->channel);
            $this->queue->setName($queue);
            $this->queue->setFlags(AMQP_DURABLE); //持久化
        }
        if (!$this->queue->declareQueue()) {
            return false;
        }
        //拒绝使用consume回调方式实时获取队列信息
        $message = $this->queue->get();
        if ($message) {
            $this->message = $message;
            return $message->getBody();
        }
        return false;
    }

    public function consumeStatus($flag = true)
    {
        // TODO: Implement consumeStatus() method.
        if ($flag) {
            if ($this->queue && $this->message) {
                return $this->queue->ack($this->message->getDeliveryTag());
            }
        }
        return false;
    }

    public function reQueue($message)
    {
        // TODO: Implement reQueue() method.
        if ($this->queueName) {
            return $this->product($message, $this->queueName);
        }
        return false;
    }

    public function close()
    {
        // TODO: Implement close() method.
        if ($this->conn) {
            $this->conn->disconnect();
            $this->conn = null;
            $this->channel = null;
            $this->exchange = null;
            $this->queue = null;
            $this->message = null;
        }
    }
}