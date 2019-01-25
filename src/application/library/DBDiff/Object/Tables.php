<?php

namespace DBDiff\Object;

use \DB\Mysql\Statement\Factory as StmtFactory;

class Tables extends Schema {

    private static function getCount($tbl_data) {
        $count = 0;
        foreach ($tbl_data as $category => $category_data) {
            $count += count($category_data);
        }
        return $count;
    }

    protected function getAddDetail($tbl_name, $db_data) {
        return [
            'name' => $tbl_name,
            'status' => 'added',
            'count' => self::getCount($db_data),
            'content' => StmtFactory::create('Table', [$tbl_name])->getAddStatement()
        ];
    }

    protected function getChangeDetail($tbl_name, $db_data) {
        $tbl_stmt = StmtFactory::create('Table', [$tbl_name]);
        $count = 0;
        $old_data = $this->{$tbl_name};
        foreach ($old_data as $category => $category_data) {
            if ($category_data != $db_data[$category]) {
                $category_name = ucfirst(trim($category, 's'));
                foreach ($category_data as $key => $data) {
                    if (isset($db_data[$category][$key])) {
                        if ($data != $db_data[$category][$key]) {
                            call_user_func_array([$tbl_stmt, 'modify'.$category_name], [$key]);
                            $count++;
                        }
                        unset($db_data[$category][$key]);
                    } else {
                        call_user_func_array([$tbl_stmt, 'drop'.$category_name], [$key]);
                        $count++;
                    }
                }
            } else {
                unset($db_data[$category]);
            }
        }
        foreach ($db_data as $category => $category_data) {
            $category_name = ucfirst(trim($category, 's'));
            foreach ($category_data as $key => $data) {
                call_user_func_array([$tbl_stmt, 'add'.$category_name], [$key]);
                $count++;
            }
        }
        return [
            'name' => $tbl_name,
            'status' => 'modified',
            'count' => $count,
            'content' => $tbl_stmt->getChangeStatement()
        ];
    }

    protected function getDeleteDetail($tbl_name) {
        return [
            'name' => $tbl_name,
            'status' => 'deleted',
            'count' => self::getCount($this->{$tbl_name}),
            'content' => StmtFactory::create('Table', [$tbl_name])->getDropStatement()
        ];
    }

}
