<?php
/**
 * Created by PhpStorm.
 * User: linyang3
 * Date: 17/2/15
 * Time: 下午2:21
 */

namespace libs\Orm;

/**
 * Class LDBNull.
 * 虚拟类用于代替LDBKernel,提高兼容和容错率.
 * @package com\cube\Orm
 * @method $this where($options)
 * @method $this orWhere($options)
 * @method $this andWhere($options)
 * @method $this order($options)
 * @method $this group($options)
 * @method $this limit($start, $length)
 * @method $this join($tableName, $type)
 * @method $this on($value)
 * @method $this asTable($name)
 */
class LDBNull
{
    /**
     * LDBNull constructor.
     */
    public function __construct()
    {
    }

    /**
     * call function.
     * @param $name
     * @param $arguments
     * @return $this|bool
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        switch ($name) {
            case 'where':
            case 'orWhere':
            case 'andWhere':
            case 'asTable':
            case 'order':
            case 'group':
            case 'limit':
            case 'join':
            case 'on':
                return $this;
            default:
                return false;
        }
    }
}