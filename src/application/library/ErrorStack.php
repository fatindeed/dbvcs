<?php

class ErrorStack extends \SplStack {

    use \Singleton;

    const E_ERROR   = 'danger';
    const E_WARNING = 'warning';
    const E_NOTICE  = 'info';

    private function __construct() {}

    public static function addError($body, $class = self::E_ERR) {
        $obj = self::getInstance();
        $obj->push(compact('class', 'body'));
    }

}