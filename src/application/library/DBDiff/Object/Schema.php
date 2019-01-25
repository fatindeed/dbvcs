<?php

namespace DBDiff\Object;

abstract class Schema extends \DBDiff\FileData {

    use \Singleton;

    protected $stmt;

    protected $name;

    public function __construct() {
        $this->name = substr(strrchr(get_called_class(), '\\'), 1);
        $this->filename = 'gitlab://db/schema/'.strtolower($this->name).'.json';
        $this->stmt = \DB\Mysql\Statement\Factory::create($this->name, [$this->name]);
        parent::__construct();
    }

    public function getName() {
        return $this->name;
    }

    public function getHeader($name) {
        $content = '--'.PHP_EOL;
        if ($this->name == 'Tables') {
            $content .= '-- Table structure for table `'.$name.'`'.PHP_EOL;
        } else {
            // -- Dumping routines for database 'crm'
            $content .= '-- Dumping '.$this->name.': '.$name.PHP_EOL;
        }
        $content .= '--'.PHP_EOL;
        return $content;
    }

    public function getChanges() {
        $db_data = $this->stmt->getData();
        $changes = [];
        foreach ($this as $name => $tbl_data) {
            if (isset($db_data[$name])) {
                if ($data != $db_data[$name]) {
                    $detail = $this->getChangeDetail($name, $db_data[$name]);
                    if ($detail['count'] > 0) {
                        $changes[] = $detail;
                    }
                }
                unset($db_data[$name]);
            } else {
                $changes[] = $this->getDeleteDetail($name);
            }
        }
        foreach ($db_data as $name => $data) {
            $changes[] = $this->getAddDetail($name, $data);
        }
        return $changes;
    }

    public function getContenct() {
        $content = '';
        $changes = $this->getChanges();
        if (count($changes) == 0) return $content;
        foreach ($changes as $change) {
            if ($change['content']) {
                $content .= $this->getHeader($change['name']).$change['content'].PHP_EOL;
            }
        }
        return $content;
    }

    protected function getAddDetail($name, $db_data) {
        return [
            'name' => $name,
            'status' => 'added',
            'count' => 1,
            'content' => $this->stmt->getAddStatement($name)
        ];
    }

    protected function getChangeDetail($name, $db_data) {
        if (strcasecmp($this->{$name}, $db_data) == 0) {
            return ['name' => $name, 'count' => 0];
        }
        $content = $this->stmt->getDropStatement($name);
        $content .= $this->stmt->getAddStatement($name);
        return [
            'name' => $name,
            'status' => 'modified',
            'count' => 1,
            'content' => $content
        ];
    }

    protected function getDeleteDetail($name) {
        return [
            'name' => $name,
            'status' => 'deleted',
            'count' => 1,
            'content' => $this->stmt->getDropStatement($name)
        ];
    }

    public function saveChanges() {
        $this->setArray($this->stmt->getData());
        $this->save();
    }

}