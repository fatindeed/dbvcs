<?php

namespace DB\Mysql\Statement;

class Table {

    private static $_instances;

    // private $statements = [];
    private $tbl_name;
    private $create_sql;
    private $specifications = [];
    private $trigger_stmt = [];
    private $field_meta;

    public static function getInstance($tbl_name) {
        if (!isset($_instances[$tbl_name])) {
            $_instances[$tbl_name] = new self($tbl_name);
        }
        return $_instances[$tbl_name];
    }

    private function __construct($tbl_name) {
        $db = \DB\Factory::create();
        $create_sql = $db->getOne('SHOW CREATE TABLE '.$tbl_name, [], 1);
        if (substr($create_sql, 0, 12) != 'CREATE TABLE') {
            throw new \UnexpectedValueException('Unexpected CREATE TABLE Syntax - '.$create_sql, 1);
        }
        $this->tbl_name = $tbl_name;
        $this->create_sql = preg_replace('/AUTO_INCREMENT=\d+\s+/', '', $create_sql);
    }

    public function getAddStatement() {
        $content = $this->create_sql.';'.PHP_EOL;
        $triggers = Factory::create('Triggers')->getTableTriggers($this->tbl_name);
        if (is_array($triggers) && count($triggers) > 0) {
            foreach ($triggers as $trigger_name => $trigger_data) {
                $content .= Factory::create('Triggers')->getAddStatement($trigger_name);
            }
        }
        return $content;
    }

    public function getChangeStatement() {
        $content = '';
        if (is_array($this->specifications) && count($this->specifications) > 0) {
            $content .= 'ALTER TABLE `'.$this->tbl_name.'`'.PHP_EOL;
            $content .= implode(','.PHP_EOL, $this->specifications).';'.PHP_EOL;
        }
        if (is_array($this->trigger_stmt) && count($this->trigger_stmt) > 0) {
            $content .= implode('', $this->trigger_stmt);
        }
        return $content;
    }

    public function getDropStatement() {
        return 'DROP TABLE IF EXISTS `'.$this->tbl_name.'`;'.PHP_EOL;
    }

    private function getColumnDefinition($col_name) {
        if (!preg_match('/^  (`'.$col_name.'`.+)$/misU', $this->create_sql, $match)) {
            throw new \UnexpectedValueException('Column['.$col_name.'] not found - '.$this->create_sql, 1);
        }
        return ltrim(rtrim($match[0], ','));
    }

    public function addColumn($col_name) {
        $this->specifications[] = 'ADD COLUMN '.$this->getColumnDefinition($col_name);
    }

    public function modifyColumn($col_name) {
        $this->specifications[] = 'MODIFY COLUMN '.$this->getColumnDefinition($col_name);
    }

    public function dropColumn($col_name) {
        $this->specifications[] = 'DROP COLUMN `'.$col_name.'`';
    }

    private function getIndexDefinition($index_name) {
        if ($index_name == 'PRIMARY') {
            if (!preg_match('/^  (PRIMARY KEY .+)$/misU', $this->create_sql, $match)) {
                throw new \UnexpectedValueException('PRIMARY KEY not found - '.$this->create_sql, 1);
            }
            return ltrim(rtrim($match[0], ','));
        } else {
            if (!preg_match('/^  (UNIQUE |FOREIGN )?(KEY `'.$index_name.'`.+)$/misU', $this->create_sql, $match)) {
                throw new \UnexpectedValueException('Index['.$index_name.'] not found - '.$this->create_sql, 1);
            }
            return ltrim(rtrim($match[0], ','));
        }
    }

    public function addIndex($index_name) {
        $this->specifications[] = 'ADD '.$this->getIndexDefinition($index_name);
    }

    public function modifyIndex($index_name) {
        $this->dropIndex($index_name);
        $this->addIndex($index_name);
    }

    public function dropIndex($index_name) {
        if ($index_name == 'PRIMARY') {
            $this->specifications[] = 'DROP PRIMARY KEY';
        } else {
            $this->specifications[] = 'DROP KEY `'.$index_name.'`';
        }
    }

    public function addTrigger($trigger_name) {
        $this->trigger_stmt[] = Factory::create('Triggers')->getAddStatement($trigger_name);
    }

    public function modifyTrigger($trigger_name) {
        $this->dropTrigger($trigger_name);
        $this->addTrigger($trigger_name);
    }

    public function dropTrigger($trigger_name) {
        $this->trigger_stmt[] = Factory::create('Triggers')->getDropStatement($trigger_name);
    }

}