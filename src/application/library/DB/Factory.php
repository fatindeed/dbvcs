<?php

namespace DB;

class Factory {

    public static function create() {
        $db_config = \Yaf\Registry::get('db_config');
        $db = call_user_func_array(['\\DB\\'.ucfirst(strtolower($db_config->type)), 'getInstance'], [$db_config]);
        return ($db instanceof DbInterface) ? $db : false;
    }

}