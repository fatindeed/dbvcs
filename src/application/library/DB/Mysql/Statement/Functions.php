<?php

namespace DB\Mysql\Statement;

class Functions {

    use \Singleton, Util;

    private function initData() {
        if (!isset($this->data)) {
            $data = [];
            $db = \DB\Factory::create();
            $rows = $db->getAll('SHOW FUNCTION STATUS WHERE Db = DATABASE()');
            foreach ($rows as $row) {
                $key = $row['Name'];
                $create_sql = $db->getOne('SHOW CREATE FUNCTION '.$key, [], 2);
                $create_sql = preg_replace('/CREATE DEFINER=\S+ FUNCTION/s', 'CREATE FUNCTION', $create_sql);
                if (substr($create_sql, 0, 15) != 'CREATE FUNCTION') {
                    throw new \UnexpectedValueException('Unexpected CREATE FUNCTION Syntax - '.$create_sql, 1);
                }
                $data[$key] = self::uniformStatement($create_sql);
            }
            $this->data = new \DataArray($data);
        }
    }

    public function getAddStatement($sp_name) {
        $this->initData();
        return self::escapeDelimiter($this->data->{$sp_name});
    }

    public function getDropStatement($sp_name) {
        return 'DROP FUNCTION IF EXISTS `'.$sp_name.'`;'.PHP_EOL;
    }

}