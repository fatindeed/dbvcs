<?php

class DataFile {

    private static $_instances = [];

    private static $data_dir;

    private $filepath;

    private function __construct($filename) {
        $this->filepath = $filename;
    }

    public static function open($filename, $use_document_root = false) {
        if (empty($filename)) {
            throw new \InvalidArgumentException('$filename not given when calling DataFile::open($filename).', 1);
        }
        if ($use_document_root) {
            $filename = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$filename;
        }
        $idx = md5($filename);
        if (!isset(self::$_instances[$idx])) {
            self::$_instances[$idx] = new self($filename);
        }
        return self::$_instances[$idx];
    }

    public function getContents() {
        if (file_exists($this->filepath)) {
            return file_get_contents($this->filepath);
        }
        return false;
    }

    public function putContents($data) {
        $dir = dirname($this->filepath);
        if (!file_exists($dir) && !mkdir($dir, 0777, true)) {
            throw new \RuntimeException('Failed to create dir: '.$dir, 1);
        }
        return file_put_contents($this->filepath, $data);
    }

    public function sha1() {
        if (file_exists($this->filepath)) {
            return sha1_file($this->filepath);
        }
        return false;
    }

}
