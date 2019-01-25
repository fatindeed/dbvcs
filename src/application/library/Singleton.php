<?php

trait Singleton {

    private static $_instances = [];

    /**
     * private construct, generally defined by using class
     */
    //private function __construct() {}
    
    public static function getInstance() {
        $class = get_called_class();
        $idx = md5($class);
        if (!isset(self::$_instances[$idx])) {
            self::$_instances[$idx] = new $class;
        }
        return self::$_instances[$idx];
    }

    public function __clone() {
        trigger_error('Cloning '.__CLASS__.' is not allowed.', E_USER_ERROR);
    }

    public function __wakeup() {
        trigger_error('Unserializing '.__CLASS__.' is not allowed.', E_USER_ERROR);
    }

}
