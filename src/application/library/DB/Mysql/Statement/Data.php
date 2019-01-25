<?php

namespace DB\Mysql\Statement;

class Data {

    private static $_instances;

    private $tbl_name;
    private $where;
    private $unique_keys;
    private $hidden_fields;
    private $data;
    private $field_meta;

    public static function getInstance($params) {
        if (empty($params['table'])) {
            throw new \InvalidArgumentException('$params[\'table\'] not given when calling DB\Mysql\Statement\\Object\\Data::getInstance($params).', 1);
        }
        $tbl_name = $params['table'];
        if (!isset($_instances[$tbl_name])) {
            $_instances[$tbl_name] = new self($params);
        }
        return $_instances[$tbl_name];
    }

    private function __construct($params) {
        $this->tbl_name = $params->table;
        $this->where = $params->where;
        $this->unique_keys = explode(',', $params->unique);
        $this->hidden_fields = explode(',', $params->hidden);
    }

    private function initData() {
        if (!isset($this->data)) {
            $data = [];
            $sql = 'SELECT * FROM `'.$this->tbl_name.'`';
            if ($this->where) {
                $sql .= ' WHERE '.$this->where;
            }
            $sql .= ' ORDER BY '.implode(',', $this->unique_keys);
            $rows = \DB\Factory::create()->getAll($sql);
            foreach ($rows as $row) {
                $key_values = [];
                foreach ($this->unique_keys as $unique_key) {
                    $key_values[] = $row[$unique_key];
                }
                $key = implode('|', $key_values);
                $data[$key] = $row;
            }
            $this->data = new \DataArray($data);
        }
    }

    public function getData() {
        $this->initData();
        $data = [];
        foreach ($this->data as $key => $row) {
            foreach ($this->hidden_fields as $field) {
                unset($row[$field]);
            }
            $data[$key] = $row;
        }
        return $data;
    }

    private function getRowData($key) {
        $this->initData();
        if (!isset($this->data->{$key})) {
            throw new \UnexpectedValueException($key.' not found for '.$this->tbl_name, 1);
        }
        $data = $this->data->{$key};
        foreach ($this->hidden_fields as $field) {
            unset($data[$field]);
        }
        return $data;
    }

    private function getWhere($key) {
        $conditions = [];
        $values = explode('|', $key);
        foreach ($this->unique_keys as $idx => $col_name) {
            $conditions[] = $col_name.'="'.$values[$idx].'"';
        }
        return ' WHERE '.implode(' AND ', $conditions);
    }

    private function columnIsNull($col_name) {
        if (!isset($this->field_meta)) {
            $db_data = Factory::create('Tables')->getTable($this->tbl_name);
            $this->field_meta = $db_data['columns'];
            unset($db_data);
        }
        return ($this->field_meta[$col_name]['Null'] == 'YES');
    }

    public function getAddStatement($key) {
        $this->initData();
        if (!isset($this->data->{$key})) {
            throw new \UnexpectedValueException($key.' not found for '.$this->tbl_name, 1);
        }
        $columns = $values = [];
        foreach ($this->data->{$key} as $col_name => $value) {
            $columns[] = '`'.$col_name.'`';
            if (strval($value) === '' && $this->columnIsNull($col_name)) {
                $values[] = 'NULL';
            } else {
                $values[] = '\''.str_replace(["\r", "\n", "\t"], ['\r', '\n', '\t'], $value).'\'';
            }
        }
        return 'INSERT INTO `'.$this->tbl_name.'` ('.implode(', ', $columns).') VALUES ('.implode(', ', $values).');'.PHP_EOL;
    }

    public function getChangeStatement($key, $before) {
        $assignment_list = [];
        $data = $this->getRowData($key);
        foreach ($data as $col_name => $value) {
            if ($value != $before[$col_name]) {
                $assignment_list[] = $col_name.'="'.str_replace(["\r", "\n", "\t"], ['\r', '\n', '\t'], $value).'"';
            }
        }
        return 'UPDATE `'.$this->tbl_name.'` SET '.implode(',', $assignment_list).$this->getWhere($key).';'.PHP_EOL;
    }

    public function getDropStatement($key) {
        return 'DELETE FROM `'.$this->tbl_name.'`'.$this->getWhere($key).';'.PHP_EOL;
    }

}