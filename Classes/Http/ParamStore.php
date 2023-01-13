<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Http;

/*

heavily borrowed from Symfony's ParameterBag and Guzzle Collection

https://github.com/guzzle/guzzle/blob/v3.5.0/src/Guzzle/Common/Collection.php

*/


class ParamStore
{

    protected $data = [];
    protected $case_sensitive;

    public function __construct($parameters = [], $case_sensitive = false)
    {
        $this->data = $parameters;
        $this->case_sensitive = $case_sensitive;
    }

    private function normalizeKey($key)
    {
        return $this->case_sensitive ? $key : strtolower($key);
    }

    public function set($key, $value, $replace = true)
    {

        $key = $this->normalizeKey($key);

        // replacing or does not have existing key filled yet
        if ($replace || !$this->has($key)) {
            $this->data[$key] = $value;
        } else {

            if (is_array($this->data[$key])) {
                $this->data[$key][] = $value;
            } else {
                $this->data[$key] = [$this->data[$key], $value];
            }
        }
    }

    public function replace(array $data)
    {

        // remove all existing items first
        $this->clear();

        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function remove($key)
    {
        unset($this->data[$this->normalizeKey($key)]);
    }

    public function clear()
    {
        $this->data = [];
    }

    public function has($key)
    {
        return isset($this->data[$this->normalizeKey($key)]);
    }

    public function get($key, $default = null)
    {

        $key = $this->normalizeKey($key);

        return $this->has($key) ? $this->data[$key] : $default;
    }

    // Returns an array of all values currently stored
    public function all()
    {
        return $this->data;
    }

    public function __toString()
    {
        return json_encode($this->data, true);
    }
}


