<?php

namespace WapplerSystems\Proxy\Event;

use ArrayAccess;
use ReturnTypeWillChange;

class ProxyEvent implements ArrayAccess
{
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    #[ReturnTypeWillChange] public function offsetSet($offset, $value)
    {

        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    #[ReturnTypeWillChange] public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    #[ReturnTypeWillChange] public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    #[ReturnTypeWillChange] public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

}

