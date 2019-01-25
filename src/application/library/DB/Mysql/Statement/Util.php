<?php

namespace DB\Mysql\Statement;

trait Util {

    private $data;

    abstract public function initData();

    public function getData() {
        $this->initData();
        return $this->data->getArray();
    }

    protected static function uniformStatement($statement) {
        return preg_replace('/[\r\n]+/', PHP_EOL, $statement);
    }

    protected static function escapeDelimiter($sql) {
        $content = 'DELIMITER ;;'.PHP_EOL;
        $content .= $sql.' ;;'.PHP_EOL;
        $content .= 'DELIMITER ;'.PHP_EOL;
        return $content;
    }

    abstract public function getAddStatement();

    abstract public function getDropStatement();

}
