<?php

namespace libs\Orm;

use ArrayIterator;
use Exception;
use libs\Utils\Arrays;

/**
 * 实现SPL接口方法 ArrayAccess, IteratorAggregate, JsonSerializable, Serializable, Countable
 * 让对象可以像数组一样访问
 *
 * Class ArrayTrait
 *
 * @author chenqionghe
 * @package libs\Orm
 */
trait ArrayTrait
{
    /**
     * 属性
     *
     * @var array
     */
    private $_attributes = [];

    /**
     * 转数组
     *
     * @return array
     */
    public function toArray()
    {
        return !empty($this->_attributes) ? $this->_attributes : [];
    }

    /**
     * 转json
     *
     * @param int $option
     * @return string
     */
    public function toJson($option = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->_attributes, $option);
    }


    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return !is_null(Arrays::get($this->_attributes, $name));
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_attributes);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if (property_exists($this, $offset)) {
            $this->$offset = null;
        } else {
            unset($this->$offset);
        }
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * @param mixed $offset
     * @param mixed $item
     */
    public function offsetSet($offset, $item)
    {
        $this->$offset = $item;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_attributes);
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->_attributes;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->_attributes);
    }

    /**
     * @param string $serialized
     * @return mixed
     */
    public function unserialize($serialized)
    {
        return $this->_attributes = unserialize($serialized);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_attributes[$name]) || array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        }
        if ($this->hasAttribute($name)) {
            return null;
        }
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;
        }
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
            return;
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        try {
            return $this->__get($name) !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        if ($this->hasAttribute($name)) {
            unset($this->_attributes[$name]);
        }
    }
}
