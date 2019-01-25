<?php

/**
 * @see http://php.net/manual/zh/class.streamwrapper.php
 */

namespace GitLab;

class StreamWrapper {

    const STREAM_NAME = 'gitlab';

    public $context;

    private $fp;

    public static function register() {
        stream_wrapper_register(self::STREAM_NAME, __CLASS__) or die('Failed to register gitlab protocol');
    }

    // public function stream_cast($cast_as) {
    //     trigger_error('cast is not implemented for gitlab schema');
    // }

    public function stream_close() {
        unset($this->fp);
        return true;
    }

    public function stream_eof() {
        return $this->fp->eof();
    }

    public function stream_flush() {
        trigger_error('flush is forbidden for gitlab schema');
    }

    public function stream_lock($operation) {
        trigger_error('lock is forbidden for gitlab schema');
    }

    public function stream_metadata($path, $option, $value) {
        trigger_error('metadata is forbidden for gitlab schema');
    }

    public function stream_open($path, $mode) {
        if (!in_array($mode, ['r', 'rb'])) {
            trigger_error($path.' is read only');
        }
        $this->fp = File::getInstance($path);
        $ret = $this->fp->open($mode);
        if (empty($ret)) {
            unset($this->fp);
        }
        return $ret;
    }

    public function stream_read($count) {
        return $this->fp->read($count);
    }

    public function stream_seek($offset, $whence = SEEK_SET) {
        return $this->fp->seek($offset, $whence);
    }

    // public function stream_set_option($option, $arg1, $arg2) {
    //     trigger_error('set_option is not implemented for gitlab schema');
    // }

    public function stream_stat() {
        return $this->fp->stat();
    }

    public function stream_tell() {
        return $this->fp->tell();
    }

    public function stream_truncate() {
        trigger_error('truncate is forbidden for gitlab schema');
    }

    public function stream_write($data) {
        trigger_error('write is forbidden for gitlab schema');
    }

    public function unlink($path) {
        trigger_error('unlink is forbidden for gitlab schema');
    }

    public function url_stat($path, $flags) {
        return File::getInstance($path)->stat();
    }

}
