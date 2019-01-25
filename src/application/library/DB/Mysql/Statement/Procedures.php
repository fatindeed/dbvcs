<?php

namespace DB\Mysql\Statement;

class Procedures {

    use \Singleton, Util;

    private function initData() {
        if (!isset($this->data)) {
            $data = [];
            $db = \DB\Factory::create();
            $rows = $db->getAll('SHOW PROCEDURE STATUS WHERE Db = DATABASE()');
            foreach ($rows as $row) {
                $key = $row['Name'];
                $create_sql = $db->getOne('SHOW CREATE PROCEDURE '.$key, [], 2);
                $create_sql = preg_replace('/CREATE DEFINER=\S+ PROCEDURE/s', 'CREATE PROCEDURE', $create_sql);
                if (substr($create_sql, 0, 16) != 'CREATE PROCEDURE') {
                    throw new \UnexpectedValueException('Unexpected CREATE PROCEDURE Syntax - '.$create_sql, 1);
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
        return 'DROP PROCEDURE IF EXISTS `'.$sp_name.'`;'.PHP_EOL;
    }

}