<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/3/10
 * Time: 下午2:34
 */

namespace Base\Queue;

/**
 * Interface IMQ.
 * @package Base\Queue
 */
interface IMQ
{
    /**
     * IMQ constructor.
     * @param $options
     */
    public function __construct($options);

    /**
     * 生产队列消息.
     * @param $message string 消息内容
     * @param $channel string (rabbit中代表routerKey,redis中代表list的keyName)
     * @return bool
     */
    public function product($message, $channel);

    /**
     * 消费队列.
     * @param $channel string (rabbit中代表routerKey,redis中代表list的keyName)
     * @param $count int 消费条数
     * @return string|bool
     */
    public function consume($channel, $count = 1);

    /**
     * 消息回滚至队列.
     * @param $message string
     * @return bool
     */
    public function reQueue($message);

    /**
     * 消息消费状态
     * @param $flag
     * @return mixed
     */
    public function consumeStatus($flag);

    /**
     * 关闭连接.
     * @return mixed
     */
    public function close();
}