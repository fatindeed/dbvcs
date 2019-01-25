<?php

namespace DB\Mysql\Statement;

class Tables {

    use \Singleton, Util;

    private $db;

    private function initData() {
        if (!isset($this->data)) {
            $data = [];
            $ignore_tables = self::getIgnoreTables();
            $this->db = \DB\Factory::create();
            // $rows = $this->db->getAll('SHOW FULL TABLES WHERE Table_Type = "BASE TABLE"', [], \PDO::FETCH_NUM);
            $rows = $this->db->getAll('SHOW FULL TABLES WHERE Table_Type != "VIEW"', [], \PDO::FETCH_NUM);
            foreach ($rows as $row) {
                $tbl_name = $row[0];
                if (in_array($tbl_name, $ignore_tables)) continue;
                $data[$tbl_name] = $this->loadMetaData($tbl_name);
            }
            $this->data = new \DataArray($data);
        }
    }

    private static function getIgnoreTables() {
        $ini_array = parse_ini_file(APP_PATH.'/conf/ignore_tables.ini');
        // sort($ini_array['tables']);
        // $content = '';
        // foreach ($ini_array['tables'] as $table) {
        //     $content .= 'tables[] = "'.$table.'"'.PHP_EOL;
        // }
        // file_put_contents(APP_PATH.'/conf/ignore_tables.ini', trim($content));
        return $ini_array['tables'];
    }

    private function loadMetaData($tbl_name) {
        $table = [];
        $rows = $this->db->getAll('SHOW COLUMNS FROM '.$tbl_name);
        if (is_array($rows) && count($rows) > 0) {
            foreach ($rows as $row) {
                $key = $row['Field'];
                unset($row['Field'], $row['Key']);
                $table['columns'][$key] = $row;
            }
        }
        $rows = $this->db->getAll('SHOW INDEX FROM '.$tbl_name);
        if (is_array($rows) && count($rows) > 0) {
            foreach ($rows as $row) {
                $key = $row['Key_name'];
                $idx = $row['Seq_in_index'];
                unset($row['Table'], $row['Key_name'], $row['Seq_in_index'], $row['Cardinality']);
                $table['index'][$key][$idx] = $row;
            }
        }
        $triggers = Factory::create('Triggers')->getTableTriggers($tbl_name);
        if ($triggers) {
            $table['triggers'] = $triggers;
        }
        return $table;
    }

    public function getTable($tbl_name) {
        $this->initData();
        return $this->data->{$tbl_name};
    }

    public function getAddStatement($tbl_name) {
        trigger_error('Use '.__NAMESPACE__.'\\Tables::getAddStatement() instead');
    }


    public function getDropStatement($tbl_name) {
        trigger_error('Use '.__NAMESPACE__.'\\Tables::getDropStatement() instead');
    }

}