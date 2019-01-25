<?php

namespace DB\Mysql\Statement;

class Factory {

    public static function create($class, $params = []) {
        return call_user_func_array([__NAMESPACE__.'\\'.$class, 'getInstance'], $params);
    }

}