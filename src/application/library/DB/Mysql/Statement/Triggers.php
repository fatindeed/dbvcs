<?php

namespace DB\Mysql\Statement;

class Triggers {

    use \Singleton, Util;

    private function initData() {
        if (!isset($this->data)) {
            $data = [];
            $rows = \DB\Factory::create()->getAll('SHOW TRIGGERS');
            foreach ($rows as $row) {
                $tbl_name = $row['Table'];
                $key = $row['Trigger'];
                $row['Statement'] = self::uniformStatement($row['Statement']);
                unset($row['Trigger'], $row['Table'], $row['Created'], $row['sql_mode'], $row['Definer'], $row['character_set_client'], $row['collation_connection'], $row['Database Collation']);
                $data[$tbl_name][$key] = $row;
            }
            $this->data = new \DataArray($data);
        }
    }

    public function getTableTriggers($tbl_name) {
        $this->initData();
        return $this->data->{$tbl_name};
    }

    public function getAddStatement($trigger_name) {
        $create_sql = \DB\Factory::create()->getOne('SHOW CREATE TRIGGER '.$trigger_name, [], 2);
        $create_sql = preg_replace('/CREATE DEFINER=\S+ TRIGGER/s', 'CREATE TRIGGER', $create_sql);
        if (substr($create_sql, 0, 14) != 'CREATE TRIGGER') {
            throw new \UnexpectedValueException('Unexpected CREATE TRIGGER Syntax - '.$create_sql, 1);
        }
        return self::escapeDelimiter($create_sql);
    }

    public function getDropStatement($trigger_name) {
        return 'DROP TRIGGER IF EXISTS `'.$trigger_name.'`;'.PHP_EOL;
    }

}