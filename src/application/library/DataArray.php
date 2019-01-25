<?php

// class DataArray extends \ArrayIterator implements \JsonSerializable {
class DataArray implements \IteratorAggregate {

    private $data;

    public function __construct($data = []) {
        $this->setArray($data);
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function __get($name) {
        if (strval($name) !== '' && array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        // trigger_error(print_r(debug_backtrace()[0], true), E_USER_NOTICE);
        return null;
    }

    public function __isset($name) {
        return isset($this->data[$name]);
    }

    public function __unset($name) {
        unset($this->data[$name]);
    }

    public function getIterator() {
        return new \ArrayIterator($this->data);
    }

    public function getArray() {
        return $this->data;
    }

    public function setArray($data = []) {
        if (is_array($data)) {
            return $this->data = $data;
        }
    }

    public static function loadJson($content) {
        $data = [];
        if (!empty($content)) {
            $data = json_decode($content, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \UnexpectedValueException(json_last_error_msg(), json_last_error());
            }
        }
        return new static($data);
    }

}