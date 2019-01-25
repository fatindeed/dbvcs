<?php

namespace DBDiff\Object;

class Data extends \DBDiff\FileData {

    private $stmt;

    protected $name;

    public function __construct($params) {
        $this->name = $params->table;
        $this->filename = 'gitlab://db/data/'.$params->table.'.json';
        $this->stmt = \DB\Mysql\Statement\Factory::create('Data', [$params]);
        parent::__construct();
    }

    public function getHeader($name) {
        $content = '--'.PHP_EOL;
        $content .= '-- Dumping data for table `'.$name.'`'.PHP_EOL;
        $content .= '--'.PHP_EOL;
        return $content;
    }

    public function getChanges() {
        $db_data = $this->stmt->getData();
        $statements = [];
        foreach ($this as $key => $data) {
            if (isset($db_data[$key])) {
                $status = 'modified';
                if ($data != $db_data[$key]) {
                    $statements[] = $this->stmt->getChangeStatement($key, $data);
                }
                unset($db_data[$key]);
            }
            else {
                if (!isset($status)) {
                    $status = 'deleted';
                }
                $statements[] = $this->stmt->getDropStatement($key);
            }
        }
        foreach ($db_data as $key => $data) {
            $status = (isset($status) ? 'modified' : 'added');
            $statements[] = $this->stmt->getAddStatement($key);
        }
        $count = count($statements);
        if ($count == 0) {
            return false;
        }
        return [
            'name' => $this->name,
            'status' => $status,
            'count' => $count,
            'content' => implode('', $statements)
        ];
    }

    public function getContenct() {
        $content = '';
        $change = $this->getChanges();
        if ($change['content']) {
            $content = $this->getHeader($change['name']).$change['content'].PHP_EOL;
        }
        return $content;
    }

    public function saveChanges() {
        $this->setArray($this->stmt->getData());
        $this->save();
    }

}