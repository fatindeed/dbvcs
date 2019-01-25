<?php

namespace DBDiff;

class FileData extends \DataArray {

    protected $filename;

    public function __construct($default = []) {
        $data = [];
        $content = \DataFile::open($this->filename)->getContents();
        if (!empty($content)) {
            $data = json_decode($content, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \UnexpectedValueException(json_last_error_msg(), json_last_error());
            }
        }
        if (is_array($default) && count($default) > 0) {
            foreach ($default as $key => $value) {
                if (!isset($data[$key])) {
                    $data[$key] = $value;
                }
            }
        }
        parent::__construct($data);
    }

    protected function getLocalPath() {
        return $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.substr($this->filename, strlen(\GitLab\StreamWrapper::STREAM_NAME.'://'));
    }

    public function save() {
        $content = json_encode($this->getArray(), JSON_PRETTY_PRINT);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \UnexpectedValueException(json_last_error_msg(), json_last_error());
        }
        return \DataFile::open($this->getLocalPath())->putContents($content);
    }

}
