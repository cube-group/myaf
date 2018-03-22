<?php

namespace libs\Utils;

/**
 * Class Dynamic
 * @package libs\Utils
 */
class Dynamic
{
    protected $body;

    public function __construct($value = null)
    {
        if($value && is_array($value)){
            $this->body = $value;
        }else{
            $this->body = array();
        }
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
        return $this->body[$name];
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
        $this->body[$name] = $value;
    }

    public function delete($name)
    {
        unset($this->body[$name]);
    }

    public function clear()
    {
        foreach ($this->body as $key => $value) {
            unset($this->body[$key]);
        }
    }
}